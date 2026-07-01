<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Eventos Controller
 *
 * @property \App\Model\Table\EventosTable $Eventos
 */
class EventosController extends AppController
{
    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->Authorization->skipAuthorization();
        $query = $this->Eventos->find();
        $query->orderBy(['Eventos.ordem' => 'DESC']);
        $eventos = $this->paginate($query);

        $this->set(compact('eventos'));
    }

    /**
     * View method
     *
     * @param string|null $id Evento id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $evento = $this->Eventos->get($id, contain: ['Apoios' => ['Items']]);
        $this->Authorization->authorize($evento);
        $this->set(compact('evento'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $evento = $this->Eventos->newEmptyEntity();
        $this->Authorization->authorize($evento);
        if ($this->request->is('post')) {
            $evento = $this->Eventos->patchEntity($evento, $this->request->getData());
            if ($this->Eventos->save($evento)) {
                $this->Flash->success(__('The evento has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The evento could not be saved. Please, try again.'));
        }
        $this->set(compact('evento'));
    }

    /**
     * Edit method
     *
     * @param string|null $id Evento id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $evento = $this->Eventos->get($id, contain: []);
        $this->Authorization->authorize($evento);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $evento = $this->Eventos->patchEntity($evento, $this->request->getData());
            if ($this->Eventos->save($evento)) {
                $this->Flash->success(__('The evento has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The evento could not be saved. Please, try again.'));
        }
        $this->set(compact('evento'));
    }

    /**
     * Delete method
     *
     * @param string|null $id Evento id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $evento = $this->Eventos->get($id);
        $this->Authorization->authorize($evento);
        if ($this->Eventos->delete($evento)) {
            $this->Flash->success(__('The evento has been deleted.'));
        } else {
            $this->Flash->error(__('The evento could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Ativar evento para votação. Só admin/editor.
     * Apenas um evento pode estar ativo.
     *
     * @param string|null $id Evento id.
     * @return \Cake\Http\Response|null
     */
    public function ativar($id = null)
    {
        $this->request->allowMethod(['post']);

        $evento = $this->Eventos->get($id);
        $this->Authorization->authorize($evento);

        $this->Eventos->updateAll(['ativo' => false], ['1 = 1']);
        $evento->ativo = true;
        $this->Eventos->save($evento);

        $this->Flash->success(__('Evento "{0}" ativado para votação.', $evento->nome));
        return $this->redirect($this->referer(['action' => 'index']));
    }

    /**
     * Select active event (legacy session-based for admin/editor preview).
     *
     * @return \Cake\Http\Response|null
     */
    public function select()
    {
        $this->Authorization->skipAuthorization();
        $this->request->allowMethod(['post']);

        $identity = $this->Authentication->getIdentity();
        if ($identity && ($identity->role === 'admin' || $identity->role === 'editor')) {
            $eventoId = (int) $this->request->getData('evento_id');

            if ($eventoId) {
                // Deactivate all events first
                $this->Eventos->updateAll(
                    ['ativo' => false],
                    ['ativo' => true]
                );

                // Activate the selected event
                $evento = $this->Eventos->get($eventoId);
                $evento->ativo = true;

                if ($this->Eventos->save($evento)) {
                    $this->request->getSession()->write('selected_evento_id', $eventoId);
                    $this->Flash->success(__('Active event changed.'));
                } else {
                    $this->Flash->error(__('Error changing active event.'));
                }
            }
        } else {
            $this->Flash->error(__('You are not authorized to change the active event.'));
        }

        return $this->redirect($this->referer('/', true));
    }
}
