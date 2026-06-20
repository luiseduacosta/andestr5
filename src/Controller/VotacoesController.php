<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Votacoes Controller
 *
 * @property \App\Model\Table\VotacoesTable $Votacoes
 */
class VotacoesController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->Authorization->skipAuthorization();
        $query = $this->Votacoes->find()
            ->contain(['Users', 'Eventos', 'Items']);

        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        if ($selectedEventoId) {
            $query->where(['Votacoes.evento_id' => $selectedEventoId]);
        }

        $itemId = $this->request->getQuery('item_id');
        if ($itemId) {
            $query->where(['Votacoes.item_id' => $itemId]);
        }

        $identity = $this->Authentication->getIdentity();
        if ($identity && $identity->role === 'relator') {
            $query->where(['Votacoes.user_id' => $identity->id]);
        }

        $votacoes = $this->paginate($query);

        $this->set(compact('votacoes'));
    }

    /**
     * View method
     *
     * @param string|null $id Votacao id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $votacao = $this->Votacoes->get($id, contain: ['Users', 'Eventos', 'Items']);
        $this->Authorization->authorize($votacao);
        $this->set(compact('votacao'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $votacao = $this->Votacoes->newEmptyEntity();
        $this->Authorization->authorize($votacao);

        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        if ($selectedEventoId) {
            $votacao->evento_id = $selectedEventoId;
        }

        $itemId = $this->request->getQuery('item_id');
        if ($itemId) {
            try {
                $itemRecord = $this->Votacoes->Items->get($itemId);
                $identity = $this->Authentication->getIdentity();
                if ($identity && $identity->role === 'relator' && str_ends_with($itemRecord->item, '99') && (int)$itemRecord->user_id !== (int)$identity->id) {
                    throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to vote on this item.'));
                }
                $votacao->item_id = (int)$itemId;
                $votacao->item = $itemRecord->item;
            } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
                // Ignore if item not found
            }
        }

        if ($this->request->is('post')) {
            $votacao = $this->Votacoes->patchEntity($votacao, $this->request->getData());
            if ($selectedEventoId) {
                $votacao->evento_id = $selectedEventoId;
            }

            $identity = $this->Authentication->getIdentity();
            if ($identity && $identity->role === 'relator') {
                $votacao->user_id = $identity->id;
            }

            // Check item access
            if ($identity && $identity->role === 'relator' && $votacao->item_id) {
                try {
                    $itemRecord = $this->Votacoes->Items->get($votacao->item_id);
                    if (str_ends_with($itemRecord->item, '99') && (int)$itemRecord->user_id !== (int)$identity->id) {
                        throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to vote on this item.'));
                    }
                } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
                    $this->Flash->error(__('Invalid item selected.'));
                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($this->Votacoes->save($votacao)) {
                $this->Flash->success(__('The votacao has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The votacao could not be saved. Please, try again.'));
        }
        $users = $this->Votacoes->Users->find('list', limit: 200)->all();
        $eventos = $this->Votacoes->Eventos->find('list', limit: 200)->all();

        $identity = $this->Authentication->getIdentity();
        $itemsQuery = $this->Votacoes->Items->find('list');
        if ($selectedEventoId) {
            $itemsQuery->innerJoinWith('Apoios')
                ->where(['Apoios.evento_id' => $selectedEventoId]);
        }
        if ($identity && $identity->role === 'relator') {
            $itemsQuery->where(['OR' => [
                ['Items.item NOT LIKE' => '%99'],
                ['Items.user_id' => $identity->id]
            ]]);
        }
        $items = $itemsQuery->all();

        $this->set(compact('votacao', 'users', 'eventos', 'items'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Votacao id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $votacao = $this->Votacoes->get($id, contain: []);
        $this->Authorization->authorize($votacao);

        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');

        if ($this->request->is(['patch', 'post', 'put'])) {
            $votacao = $this->Votacoes->patchEntity($votacao, $this->request->getData());
            if ($selectedEventoId) {
                $votacao->evento_id = $selectedEventoId;
            }

            $identity = $this->Authentication->getIdentity();
            if ($identity && $identity->role === 'relator') {
                $votacao->user_id = $identity->id;
            }

            // Check item access
            if ($identity && $identity->role === 'relator' && $votacao->item_id) {
                try {
                    $itemRecord = $this->Votacoes->Items->get($votacao->item_id);
                    if (str_ends_with($itemRecord->item, '99') && (int)$itemRecord->user_id !== (int)$identity->id) {
                        throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to vote on this item.'));
                    }
                } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
                    $this->Flash->error(__('Invalid item selected.'));
                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($this->Votacoes->save($votacao)) {
                $this->Flash->success(__('The votacao has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The votacao could not be saved. Please, try again.'));
        }
        $users = $this->Votacoes->Users->find('list', limit: 200)->all();
        $eventos = $this->Votacoes->Eventos->find('list', limit: 200)->all();

        $identity = $this->Authentication->getIdentity();
        $itemsQuery = $this->Votacoes->Items->find('list');
        if ($selectedEventoId) {
            $itemsQuery->innerJoinWith('Apoios')
                ->where(['Apoios.evento_id' => $selectedEventoId]);
        }
        if ($identity && $identity->role === 'relator') {
            $itemsQuery->where(['OR' => [
                ['Items.item NOT LIKE' => '%99'],
                ['Items.user_id' => $identity->id]
            ]]);
        }
        $items = $itemsQuery->all();

        $this->set(compact('votacao', 'users', 'eventos', 'items'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Votacao id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $votacao = $this->Votacoes->get($id);
        $this->Authorization->authorize($votacao);
        if ($this->Votacoes->delete($votacao)) {
            $this->Flash->success(__('The votacao has been deleted.'));
        } else {
            $this->Flash->error(__('The votacao could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Report method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function report()
    {
        $this->Authorization->skipAuthorization();

        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        $trInput = $this->request->getQuery('trs');
        $items = [];
        $trList = [];

        if ($trInput !== null && trim($trInput) !== '') {
            $parts = explode(',', $trInput);
            foreach ($parts as $part) {
                $val = trim($part);
                if (is_numeric($val)) {
                    $trList[] = (int)$val;
                }
            }

            if (!empty($trList) && $selectedEventoId) {
                $items = $this->Votacoes->Items->find()
                    ->contain(['Apoios', 'Votacoes' => function ($q) use ($selectedEventoId) {
                        return $q->where(['Votacoes.evento_id' => $selectedEventoId])->contain(['Users']);
                    }])
                    ->innerJoinWith('Apoios')
                    ->where([
                        'Apoios.evento_id' => $selectedEventoId,
                        'Items.tr IN' => $trList
                    ])
                    ->order(['Items.tr' => 'ASC', 'Items.item' => 'ASC'])
                    ->all();
            }
        }

        $this->set(compact('items', 'trInput', 'trList'));
    }
}
