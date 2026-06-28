<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\ORM\Query\SelectQuery;

/**
 * Gts Controller
 *
 * @property \App\Model\Table\GtsTable $Gts
 */
class GtsController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->Authorization->skipAuthorization();
        
        $query = $this->Gts->find();
        $gts = $this->paginate($query);

        $this->set(compact('gts'));
    }

    /**
     * View method
     *
     * @param string|null $id Gt id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $this->Authorization->skipAuthorization();
        
        $gt = $this->Gts->get($id, contain: ['Apoios']);

        $this->set(compact('gt'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $this->Authorization->skipAuthorization();
        
        $gt = $this->Gts->newEmptyEntity();
        if ($this->request->is('post')) {
            $gt = $this->Gts->patchEntity($gt, $this->request->getData());
            if ($this->Gts->save($gt)) {
                $this->Flash->success(__('The gt has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The gt could not be saved. Please, try again.'));
        }
        $this->set(compact('gt'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Gt id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $this->Authorization->skipAuthorization();
        
        $gt = $this->Gts->get($id);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $gt = $this->Gts->patchEntity($gt, $this->request->getData());
            if ($this->Gts->save($gt)) {
                $this->Flash->success(__('The gt has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The gt could not be saved. Please, try again.'));
        }
        $this->set(compact('gt'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Gt id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->Authorization->skipAuthorization();
        
        $this->request->allowMethod(['post', 'delete']);
        $gt = $this->Gts->get($id);
        if ($this->Gts->delete($gt)) {
            $this->Flash->success(__('The gt has been deleted.'));
        } else {
            $this->Flash->error(__('The gt could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
