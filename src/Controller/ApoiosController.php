<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Apoios Controller
 *
 * @property \App\Model\Table\ApoiosTable $Apoios
 */
class ApoiosController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->Authorization->skipAuthorization();
        $query = $this->Apoios->find()
            ->contain(['Eventos', 'Gts']);
        
        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        if ($selectedEventoId) {
            $query->where(['Apoios.evento_id' => $selectedEventoId]);
        }

        $search = $this->request->getQuery('search');
        if (!empty($search)) {
            $query->where([
                'OR' => [
                    'Apoios.autor LIKE' => '%' . $search . '%',
                    'Apoios.texto LIKE' => '%' . $search . '%',
                ]
            ]);
        }

        $apoios = $this->paginate($query);
        $this->set(compact('apoios'));
    }

    /**
     * View method
     *
     * @param string|null $id Apoio id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->Authorization->skipAuthorization();
        $identity = $this->Authentication->getIdentity();
        
        $itemsQueryBuilder = function ($query) use ($identity) {
            $query->orderBy(['Items.item' => 'ASC']);
            if ($identity && $identity->role === 'relator') {
                $query->where(['OR' => [
                    ['Items.item NOT LIKE' => '%.99'],
                    ['Items.user_id' => $identity->id],
                ]]);
            }
            return $query;
        };

        $apoio = $this->Apoios->get($id, contain: [
            'Eventos',
            'Gts',
            'Items' => $itemsQueryBuilder
        ]);
        $this->set(compact('apoio'));
    }

    public function viewtr()
    {
        $this->Authorization->skipAuthorization();
        $eventoId = $this->request->getQuery('evento_id');
        if (empty($eventoId)) {
            throw new \Cake\Datasource\Exception\RecordNotFoundException(__('Evento não especificado'));
        }
        $tr = $this->request->getQuery('tr');
        if (empty($tr)) {
            throw new \Cake\Datasource\Exception\RecordNotFoundException(__('TR não especificado'));
        }

        $identity = $this->Authentication->getIdentity();
        $itemsQueryBuilder = function ($query) use ($identity) {
            $query->orderBy(['Items.item' => 'ASC']);
            if ($identity && $identity->role === 'relator') {
                $query->where(['OR' => [
                    ['Items.item NOT LIKE' => '%.99'],
                    ['Items.user_id' => $identity->id],
                ]]);
            }
            return $query;
        };

        $apoio = $this->Apoios->find()
            ->contain([
                'Eventos',
                'Gts',
                'Items' => $itemsQueryBuilder
            ])
            ->where([
                'Apoios.numero_texto' => $tr,
                'Apoios.evento_id' => $eventoId
            ])
            ->first();

        if (!$apoio) {
            throw new \Cake\Datasource\Exception\RecordNotFoundException(__('TR não encontrado'));
        }
        $this->set(compact('apoio'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $apoio = $this->Apoios->newEmptyEntity();
        $this->Authorization->authorize($apoio);
        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        if ($selectedEventoId) {
            $apoio->evento_id = $selectedEventoId;
        }
        if ($this->request->is('post')) {
            $apoio = $this->Apoios->patchEntity($apoio, $this->request->getData());
            if ($selectedEventoId) {
                $apoio->evento_id = $selectedEventoId;
            }
            if ($this->Apoios->save($apoio)) {
                $this->Flash->success(__('Texto de apoio salvo.'));

                return $this->redirect(['action' => 'index']);
            }
            
            $errors = [];
            if ($apoio->getErrors()) {
                foreach ($apoio->getErrors() as $field => $fieldErrors) {
                    foreach ($fieldErrors as $rule => $message) {
                        $errors[] = $message;
                    }
                }
            }
            if (!empty($errors)) {
                $this->Flash->error(__('Texto de apoio não pôde ser salvo: {0}', implode(', ', array_unique($errors))));
            } else {
                $this->Flash->error(__('Texto de apoio não pôde ser salvo. Tente novamente.'));
            }
        }
        $eventos = $this->Apoios->Eventos->find('list', limit: 200)->all();
        $gts = $this->Apoios->Gts->find('list', limit: 20)->all();
        $this->set(compact('apoio', 'eventos', 'gts'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Apoio id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $apoio = $this->Apoios->get($id, contain: []);
        $this->Authorization->authorize($apoio);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $apoio = $this->Apoios->patchEntity($apoio, $this->request->getData());
            if ($this->Apoios->save($apoio)) {
                $this->Flash->success(__('Texto de apoio salvo.'));

                return $this->redirect(['action' => 'index']);
            }
            
            $errors = [];
            if ($apoio->getErrors()) {
                foreach ($apoio->getErrors() as $field => $fieldErrors) {
                    foreach ($fieldErrors as $rule => $message) {
                        $errors[] = $message;
                    }
                }
            }
            if (!empty($errors)) {
                $this->Flash->error(__('Texto de apoio não pôde ser salvo: {0}', implode(', ', array_unique($errors))));
            } else {
                $this->Flash->error(__('Texto de apoio não pôde ser salvo. Tente novamente.'));
            }
        }
        $eventos = $this->Apoios->Eventos->find('list', limit: 200)->all();
        $gts = $this->Apoios->Gts->find('list', limit: 20)->all();
        $this->set(compact('apoio', 'eventos', 'gts'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Apoio id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $apoio = $this->Apoios->get($id);
        $this->Authorization->authorize($apoio);
        if ($this->Apoios->delete($apoio)) {
            $this->Flash->success(__('Texto de apoio excluído.'));
        } else {
            $this->Flash->error(__('Texto de apoio não pôde ser excluído. Tente novamente.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
