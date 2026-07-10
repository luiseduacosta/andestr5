<?php
declare(strict_types=1);

namespace App\Controller;

/**
 * Users Controller
 *
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * beforeFilter method
     *
     * @param \Cake\Event\EventInterface $event Event
     * @return void
     */
    public function beforeFilter(\Cake\Event\EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authentication->allowUnauthenticated(['login']);
    }

    /**
     * Login method.
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function login()
    {
        $this->Authorization->skipAuthorization();
        $result = $this->Authentication->getResult();

        if ($result && $result->isValid()) {
            $redirect = $this->request->getQuery('redirect', [
                'controller' => 'Eventos',
                'action' => 'index',
            ]);

            return $this->redirect($redirect);
        }
        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error(__('Invalid username or password'));
        }
    }

    /**
     * Logout method.
     *
     * @return \Cake\Http\Response|null
     */
    public function logout()
    {
        $this->Authorization->skipAuthorization();
        $result = $this->Authentication->getResult();
        if ($result && $result->isValid()) {
            $this->Authentication->logout();
        }
        $this->request->getSession()->delete('impersonated_by');

        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->Authorization->authorize($this->Users->newEmptyEntity(), 'index');
        $query = $this->Users->find();
        // $query->contain([
        //     'Votacoes' => fn($q)
        //         => $q->where(['Votacoes.evento_id'
        //         => $this->request->getSession()->read('selected_evento_id')])]);
        $users = $this->paginate($query);
        $this->set(compact('users'));
    }

    /**
     * View method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Renders view
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $user = $this->Users->get($id, contain: [
            'Votacoes' => [
                'Users',
                'Eventos',
                'queryBuilder' =>
                    fn($q)
                        => $q->where(['Votacoes.evento_id'
                        => $this->request->getSession()->read('selected_evento_id')])
            ]
        ]);
        $this->Authorization->authorize($user);
        $this->set(compact('user'));
    }

    /**
     * Add method
     *
     * @return \Cake\Http\Response|null|void Redirects on successful add, renders view otherwise.
     */
    public function add()
    {
        $user = $this->Users->newEmptyEntity();
        $this->Authorization->authorize($user);
        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Edit method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null|void Redirects on successful edit, renders view otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function edit($id = null)
    {
        $user = $this->Users->get($id, contain: []);
        $this->Authorization->authorize($user);
        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();

            // Prevent privilege escalation: restrict role editing to admin or editor
            $identity = $this->Authentication->getIdentity();
            if (!$identity || ($identity->role !== 'admin' && $identity->role !== 'editor')) {
                unset($data['role']);
            }

            $user = $this->Users->patchEntity($user, $data);
            if ($this->Users->save($user)) {
                $this->Flash->success(__('The user has been saved.'));

                if ($identity && ($identity->role === 'admin' || $identity->role === 'editor')) {
                    return $this->redirect(['action' => 'index']);
                }
                return $this->redirect(['action' => 'view', $user->id]);
            }
            $this->Flash->error(__('The user could not be saved. Please, try again.'));
        }
        $this->set(compact('user'));
    }

    /**
     * Delete method
     *
     * @param string|null $id User id.
     * @return \Cake\Http\Response|null Redirects to index.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function delete($id = null)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->Users->get($id);
        $this->Authorization->authorize($user);
        if ($this->Users->delete($user)) {
            $this->Flash->success(__('The user has been deleted.'));
        } else {
            $this->Flash->error(__('The user could not be deleted. Please, try again.'));
        }

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Impersonate method – allows an admin to switch to a different user identity.
     *
     * The current (admin) user ID is stored in the session so it can be restored later.
     *
     * @param string|null $id Target user id.
     * @return \Cake\Http\Response|null Redirects on success, renders error otherwise.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function impersonate($id = null)
    {
        $this->request->allowMethod(['post']);
        $targetUser = $this->Users->get($id);
        $this->Authorization->authorize($targetUser, 'impersonate');

        $session = $this->request->getSession();
        $identity = $this->Authentication->getIdentity();

        // Prevent nested impersonation
        if (!$session->check('impersonated_by')) {
            $session->write('impersonated_by', $identity->id);
        }

        // Replace the current session identity with the target user
        $this->Authentication->setIdentity($targetUser);

        $this->Flash->success(__('You are now impersonating {0}.', h($targetUser->username)));

        return $this->redirect(['controller' => 'Eventos', 'action' => 'index']);
    }

    /**
     * Stop impersonating and restore the original admin identity.
     *
     * @return \Cake\Http\Response|null Redirects to Users index.
     */
    public function stopImpersonate()
    {
        $this->Authorization->skipAuthorization();
        $session = $this->request->getSession();
        $originalAdminId = $session->read('impersonated_by');

        if (!$originalAdminId) {
            $this->Flash->error(__('No impersonation session found.'));

            return $this->redirect(['action' => 'index']);
        }

        $adminUser = $this->Users->get($originalAdminId);

        // Restore the original admin identity
        $this->Authentication->setIdentity($adminUser);
        $session->delete('impersonated_by');

        $this->Flash->success(__('Impersonation ended. You are now logged in as {0}.', h($adminUser->username)));

        return $this->redirect(['action' => 'index']);
    }
}
