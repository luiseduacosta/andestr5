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
        $apoio = $this->Apoios->get($id, contain: ['Eventos', 'Gts', 'Items' => ['sort' => ['Items.item' => 'ASC']]]);
        $this->set(compact('apoio'));
    }

    public function viewtr($tr = null)
    {
        $this->Authorization->skipAuthorization();
        $eventoId = $this->request->getQuery('evento_id');
        if (empty($eventoId)) {
            throw new \Cake\Datasource\Exception\RecordNotFoundException(__('Evento não especificado'));
        }
        $tr = $this->request->getQuery('tr') ?: $tr;
        if (empty($tr)) {
            throw new \Cake\Datasource\Exception\RecordNotFoundException(__('TR não especificado'));
        }

        $apoio = $this->Apoios->find()->contain(['Eventos', 'Items'])->where(['Apoios.numero_texto' => $tr, 'Apoios.evento_id' => $eventoId])->first();
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
            $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
            if ($selectedEventoId) {
                $apoio->evento_id = $selectedEventoId;
            }
            if ($this->Apoios->save($apoio)) {
                $this->Flash->success(__('The apoio has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The apoio could not be saved. Please, try again.'));
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
            $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
            if ($selectedEventoId) {
                $apoio->evento_id = $selectedEventoId;
            }
            if ($this->Apoios->save($apoio)) {
                $this->Flash->success(__('The apoio has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The apoio could not be saved. Please, try again.'));
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
            $this->Flash->success(__('The apoio has been deleted.'));
        } else {
            $this->Flash->error(__('The apoio could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
