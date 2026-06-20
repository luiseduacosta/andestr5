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
            ->contain(['Eventos']);
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
        $apoio = $this->Apoios->get($id, contain: ['Eventos', 'Items']);
        $this->Authorization->authorize($apoio);
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
        if ($this->request->is('post')) {
            $apoio = $this->Apoios->patchEntity($apoio, $this->request->getData());
            if ($this->Apoios->save($apoio)) {
                $this->Flash->success(__('The apoio has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The apoio could not be saved. Please, try again.'));
        }
        $eventos = $this->Apoios->Eventos->find('list', limit: 200)->all();
        $this->set(compact('apoio', 'eventos'));
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
                $this->Flash->success(__('The apoio has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The apoio could not be saved. Please, try again.'));
        }
        $eventos = $this->Apoios->Eventos->find('list', limit: 200)->all();
        $this->set(compact('apoio', 'eventos'));
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
            $this->Flash->success(__('The apoio has been deleted.'));
        } else {
            $this->Flash->error(__('The apoio could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
