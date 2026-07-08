<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Items Controller
 *
 * @property \App\Model\Table\ItemsTable $Items
 */
class ItemsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->Authorization->skipAuthorization();

        $session = $this->request->getSession();

        // Handle TR filter: read from query param, persist in session
        $trFilter = $this->request->getQuery('tr_filter');
        if ($trFilter !== null) {
            if ($trFilter === '' || $trFilter === 'all') {
                $session->delete('items_tr_filter');
                $trFilter = null;
            } else {
                $session->write('items_tr_filter', $trFilter);
            }
        } else {
            $trFilter = $session->read('items_tr_filter');
        }

        $query = $this->Items->find()
            ->orderBy(['Items.item' => 'ASC'])
            ->contain(['Apoios', 'Votacoes']);

        $selectedEventoId = $session->read('selected_evento_id');
        if ($selectedEventoId) {
            $query->innerJoinWith('Apoios')
                ->where(['Apoios.evento_id' => $selectedEventoId]);
        }

        $identity = $this->Authentication->getIdentity();
        if ($identity && $identity->role === 'relator') {
            $query->where(['OR' => [
                ['Items.item NOT LIKE' => '%.99'],
                ['Items.user_id' => $identity->id]
            ]]);
        }

        // Apply TR filter (item field format: "XX.YY" – filter by first 2 digits)
        if ($trFilter) {
            $query->where(['Items.item LIKE' => $trFilter . '.%']);
        }

        // Build available TR options from the same base conditions (without the TR filter)
        $trOptionsQuery = $this->Items->find();
        if ($selectedEventoId) {
            $trOptionsQuery->innerJoinWith('Apoios')
                ->where(['Apoios.evento_id' => $selectedEventoId]);
        }
        if ($identity && $identity->role === 'relator') {
            $trOptionsQuery->where(['OR' => [
                ['Items.item NOT LIKE' => '%.99'],
                ['Items.user_id' => $identity->id]
            ]]);
        }
        $trOptionsRows = $trOptionsQuery
            ->select(['tr_prefix' => $trOptionsQuery->newExpr('SUBSTR(Items.item, 1, 2)')])
            ->distinct()
            ->order(['tr_prefix' => 'ASC'])
            ->all();

        $trOptions = [];
        foreach ($trOptionsRows as $row) {
            $prefix = $row->tr_prefix;
            if ($prefix !== null && $prefix !== '') {
                $trOptions[$prefix] = $prefix;
            }
        }

        $items = $this->paginate($query);

        $this->set(compact('items', 'trOptions', 'trFilter'));
    }

    /**
     * View method
     *
     * @param string|null $id Item id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->Authorization->skipAuthorization();
        $identity = $this->Authentication->getIdentity();
        $votacoesContain = [
            'Users',
            'Eventos',
            'sort' => ['Votacoes.data' => 'DESC', 'Votacoes.id' => 'DESC']];

        if ($identity && $identity->role === 'relator') {
            $userGrupo = (int)substr((string)$identity->username, 5);
            $votacoesContain['queryBuilder'] = function ($query) use ($userGrupo) {
                return $query->where(['Votacoes.grupo' => $userGrupo]);
            };
        }

        $item = $this->Items->get($id, contain: [
            'Apoios',
            'Votacoes' => $votacoesContain,
        ]);
        $this->Authorization->authorize($item);
        $this->set(compact('item'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $item = $this->Items->newEmptyEntity();
        $this->Authorization->authorize($item);
        if ($this->request->is('post')) {
            $item = $this->Items->patchEntity($item, $this->request->getData());
            $identity = $this->Authentication->getIdentity();
            if ($identity) {
                $item->user_id = $identity->id;
            }
            if ($this->Items->save($item)) {
                $this->Flash->success(__('The item has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The item could not be saved. Please, try again.'));
        }
        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        $apoiosQuery = $this->Items->Apoios->find('list', ['keyField' => 'id', 'valueField' => 'numero_texto']);
        if ($selectedEventoId) {
            $apoiosQuery->where(['Apoios.evento_id' => $selectedEventoId]);
        }
        $apoios = $apoiosQuery->all();
        $this->set(compact('item', 'apoios'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Item id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $item = $this->Items->get($id, contain: []);
        $this->Authorization->authorize($item);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $item = $this->Items->patchEntity($item, $this->request->getData());
            if ($this->Items->save($item)) {
                $this->Flash->success(__('The item has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The item could not be saved. Please, try again.'));
        }
        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        $apoiosQuery = $this->Items->Apoios->find('list');
        if ($selectedEventoId) {
            $apoiosQuery->where(['Apoios.evento_id' => $selectedEventoId]);
        }
        $apoios = $apoiosQuery->all();
        $this->set(compact('item', 'apoios'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Item id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $item = $this->Items->get($id);
        $this->Authorization->authorize($item);
        if ($this->Items->delete($item)) {
            $this->Flash->success(__('The item has been deleted.'));
        } else {
            $this->Flash->error(__('The item could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
