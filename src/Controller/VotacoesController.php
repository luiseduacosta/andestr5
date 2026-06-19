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
        $query = $this->Votacoes->find()
            ->contain(['Users', 'Eventos', 'Items']);
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
        if ($this->request->is('post')) {
            $votacao = $this->Votacoes->patchEntity($votacao, $this->request->getData());
            if ($this->Votacoes->save($votacao)) {
                $this->Flash->success(__('The votacao has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The votacao could not be saved. Please, try again.'));
        }
        $users = $this->Votacoes->Users->find('list', limit: 200)->all();
        $eventos = $this->Votacoes->Eventos->find('list', limit: 200)->all();
        $items = $this->Votacoes->Items->find('list', limit: 200)->all();
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
        if ($this->request->is(['patch', 'post', 'put'])) {
            $votacao = $this->Votacoes->patchEntity($votacao, $this->request->getData());
            if ($this->Votacoes->save($votacao)) {
                $this->Flash->success(__('The votacao has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The votacao could not be saved. Please, try again.'));
        }
        $users = $this->Votacoes->Users->find('list', limit: 200)->all();
        $eventos = $this->Votacoes->Eventos->find('list', limit: 200)->all();
        $items = $this->Votacoes->Items->find('list', limit: 200)->all();
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
        if ($this->Votacoes->delete($votacao)) {
            $this->Flash->success(__('The votacao has been deleted.'));
        } else {
            $this->Flash->error(__('The votacao could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
