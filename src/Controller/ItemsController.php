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
            ->contain(['Votacoes']);

        $selectedEventoId = $session->read('selected_evento_id');
        if ($selectedEventoId) {
            $query->matching('Apoios', function ($q) use ($selectedEventoId) {
                return $q->where(['Apoios.evento_id' => $selectedEventoId]);
            });
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

        // Load Apoios data for the view (CakePHP reuses the matching join)
        $query->contain(['Apoios']);

        // Build available TR options from the same base conditions (without the TR filter)
        $trOptionsQuery = $this->Items->find();
        if ($selectedEventoId) {
            $trOptionsQuery->matching('Apoios', function ($q) use ($selectedEventoId) {
                return $q->where(['Apoios.evento_id' => $selectedEventoId]);
            });
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
        $identity = $this->Authentication->getIdentity();
        $votacoesContain = [
            'Users',
            'Eventos',
            'sort' => ['Votacoes.data' => 'DESC', 'Votacoes.id' => 'DESC']];

        if ($identity && $identity->role === 'relator') {
            // Extract the group number from the "grupoX" username format.
            // Username is expected to follow the pattern "grupo" followed by digits.
            $userGrupo = preg_match('/^grupo(\d+)$/i', (string)$identity->username, $m) ? (int)$m[1] : 0;
            $votacoesContain['queryBuilder'] = function ($query) use ($userGrupo) {
                return $query->where(['Votacoes.grupo' => $userGrupo]);
            };
        }

        $item = $this->Items->get($id, contain: [
            'Apoios',
            'Votacoes' => $votacoesContain,
        ]);
        $session = $this->request->getSession();
        $session->write('items_tr_filter', $item->tr);
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

            $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
            if ($item->apoio_id) {
                $apoio = $this->Items->Apoios->find()
                    ->where(['id' => $item->apoio_id])
                    ->first();
                if ($apoio) {
                    if ($selectedEventoId && (int)$apoio->evento_id !== (int)$selectedEventoId) {
                        $item->setError('apoio_id', __('O apoio não pertence ao evento selecionado.'));
                    }
                    if ((int)$apoio->numero_texto !== (int)$item->tr) {
                        $item->setError('tr', __('Apoios.numero_texto deve ser igual ao Item.tr.'));
                    }
                    $expectedPrefix = str_pad((string)$apoio->numero_texto, 2, '0', STR_PAD_LEFT);
                    $actualPrefix = substr((string)$item->item, 0, 2);
                    if ($actualPrefix !== $expectedPrefix) {
                        $item->setError('item', __('Os dois primeiros dígitos do item devem ser iguais a Apoios.numero_texto ({0}).', $expectedPrefix));
                    }
                } else {
                    $item->setError('apoio_id', __('Apoio não encontrado.'));
                }
            } else {
                $item->setError('apoio_id', __('Apoio é obrigatório.'));
            }

            if ($this->Items->save($item)) {
                $this->Flash->success(__('Item salvo.'));

                return $this->redirect(['action' => 'view', $item->id]);
            }
            
            $errors = [];
            if ($item->getErrors()) {
                foreach ($item->getErrors() as $field => $fieldErrors) {
                    foreach ($fieldErrors as $rule => $message) {
                        $errors[] = $message;
                    }
                }
            }
            if (!empty($errors)) {
                $this->Flash->error(__('Item não pôde ser salvo: {0}', implode(', ', array_unique($errors))));
            } else {
                $this->Flash->error(__('Item não pôde ser salvo. Tente novamente.'));
            }
        }
        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        $apoiosQuery = $this->Items->Apoios->find('list');
        $apoiosQuery->orderBy(['Apoios.numero_texto' => 'ASC']);
        if ($selectedEventoId) {
            $apoiosQuery->where(['Apoios.evento_id' => $selectedEventoId]);
        }
        $trFilter = $this->request->getSession()->read('items_tr_filter');
        if ($trFilter) {
            $searchQuery = $this->Items->Apoios->find()
                ->where(['Apoios.numero_texto' => $trFilter]);
            if ($selectedEventoId) {
                $searchQuery->where(['Apoios.evento_id' => $selectedEventoId]);
            }
            $matchingApoio = $searchQuery->first();
            if ($matchingApoio) {
                $item->apoio_id = $matchingApoio->id;
            }
        }
        $nextItemValue = '';
        if ($trFilter) {
            $prefix = str_pad((string)$trFilter, 2, '0', STR_PAD_LEFT);
            $nextItemValue = $prefix . '.01';
            if (isset($matchingApoio) && $matchingApoio) {
                $itemsList = $this->Items->find()
                    ->where(['apoio_id' => $matchingApoio->id])
                    ->all();
                $maxSuffix = 0;
                foreach ($itemsList as $existingItem) {
                    $itemCode = $existingItem->item;
                    if (preg_match('/^\d{2}\.(\d{2})$/', $itemCode, $matches)) {
                        $suffix = (int)$matches[1];
                        if ($suffix < 99 && $suffix > $maxSuffix) {
                            $maxSuffix = $suffix;
                        }
                    }
                }
                if ($maxSuffix > 0) {
                    $nextSuffix = str_pad((string)($maxSuffix + 1), 2, '0', STR_PAD_LEFT);
                    $nextItemValue = $prefix . '.' . $nextSuffix;
                }
            }
        }
        $apoios = $apoiosQuery->all();
        $this->set(compact('item', 'apoios', 'trFilter', 'nextItemValue'));
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
            // Prevent user_id from being changed via form tampering;
            // ownership is managed server-side after authorization.
            $data = $this->request->getData();
            unset($data['user_id']);
            $item = $this->Items->patchEntity($item, $data);

            $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
            if ($item->apoio_id) {
                $apoio = $this->Items->Apoios->find()
                    ->where(['id' => $item->apoio_id])
                    ->first();
                if ($apoio) {
                    if ($selectedEventoId && (int)$apoio->evento_id !== (int)$selectedEventoId) {
                        $item->setError('apoio_id', __('O apoio não pertence ao evento selecionado.'));
                    }
                    if ((int)$apoio->numero_texto !== (int)$item->tr) {
                        $item->setError('tr', __('Apoios.numero_texto deve ser igual ao Item.tr.'));
                    }
                    $expectedPrefix = str_pad((string)$apoio->numero_texto, 2, '0', STR_PAD_LEFT);
                    $actualPrefix = substr((string)$item->item, 0, 2);
                    if ($actualPrefix !== $expectedPrefix) {
                        $item->setError('item', __('Os dois primeiros dígitos do item devem ser iguais a Apoios.numero_texto ({0}).', $expectedPrefix));
                    }
                } else {
                    $item->setError('apoio_id', __('Apoio não encontrado.'));
                }
            } else {
                $item->setError('apoio_id', __('Apoio é obrigatório.'));
            }

            if ($this->Items->save($item)) {
                $this->Flash->success(__('Item salvo.'));

                return $this->redirect(['action' => 'index']);
            }
            
            $errors = [];
            if ($item->getErrors()) {
                foreach ($item->getErrors() as $field => $fieldErrors) {
                    foreach ($fieldErrors as $rule => $message) {
                        $errors[] = $message;
                    }
                }
            }
            if (!empty($errors)) {
                $this->Flash->error(__('Item não pôde ser salvo: {0}', implode(', ', array_unique($errors))));
            } else {
                $this->Flash->error(__('Item não pôde ser salvo. Tente novamente.'));
            }
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
            $this->Flash->success(__('Item excluído.'));
        } else {
            $this->Flash->error(__('Item não pôde ser excluído. Tente novamente.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}
