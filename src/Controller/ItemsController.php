<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\ORM\Query\SelectQuery;

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

        $selectedEventoId = $session->read('selected_evento_id');

        $query = $this->Items->find()
            ->orderBy(['Items.item' => 'ASC'])
            ->contain(['Votacoes', 'Apoios']);
        $this->applyBaseConditions($query, $selectedEventoId);

        // Apply TR filter (item field format: "XX.YY" – filter by first 2 digits)
        if ($trFilter) {
            $query->where(['Items.item LIKE' => $trFilter . '.%']);
        }

        // Build available TR options from the same base conditions (without the TR filter)
        $trOptionsQuery = $this->Items->find();
        $this->applyBaseConditions($trOptionsQuery, $selectedEventoId);
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
            'sort' => ['Votacoes.data' => 'DESC', 'Votacoes.id' => 'DESC'],
        ];

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
        // Authorize before any side effects so a denied request does not
        // mutate the persisted TR filter.
        $this->Authorization->authorize($item);

        // Persist the TR filter as a 2-digit prefix so it matches the
        // "XX.YY" item code format used by index()'s LIKE clause.
        $session = $this->request->getSession();
        $session->write('items_tr_filter', str_pad((string)$item->tr, 2, '0', STR_PAD_LEFT));

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
        $isPost = $this->request->is('post');
        if ($isPost) {
            $item = $this->Items->patchEntity($item, $this->request->getData());
            $identity = $this->Authentication->getIdentity();
            if ($identity) {
                $item->user_id = $identity->id;
            }

            $this->validateApoioConsistency($item);

            if ($this->Items->save($item)) {
                $this->Flash->success(__('Item salvo.'));

                return $this->redirect(['action' => 'view', $item->id]);
            }

            $this->flashEntityErrors($item);
        }

        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        $apoiosQuery = $this->Items->Apoios->find('list');
        $apoiosQuery->orderBy(['Apoios.numero_texto' => 'ASC']);
        if ($selectedEventoId) {
            $apoiosQuery->where(['Apoios.evento_id' => $selectedEventoId]);
        }

        $trFilter = $this->request->getSession()->read('items_tr_filter');
        $matchingApoio = null;
        if ($trFilter) {
            $searchQuery = $this->Items->Apoios->find()
                ->where(['Apoios.numero_texto' => (int)$trFilter]);
            if ($selectedEventoId) {
                $searchQuery->where(['Apoios.evento_id' => $selectedEventoId]);
            }
            $matchingApoio = $searchQuery->first();
            // Only pre-select the apoio when entering the form; never overwrite
            // the user's submitted value after a failed POST.
            if ($matchingApoio && !$isPost) {
                $item->apoio_id = $matchingApoio->id;
            }
        }

        $nextItemValue = '';
        if ($trFilter) {
            $prefix = str_pad((string)$trFilter, 2, '0', STR_PAD_LEFT);
            $nextItemValue = $prefix . '.01';
            if ($matchingApoio) {
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

            $this->validateApoioConsistency($item);

            if ($this->Items->save($item)) {
                $this->Flash->success(__('Item salvo.'));

                return $this->redirect(['action' => 'index']);
            }

            $this->flashEntityErrors($item);
        }
        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        $apoiosQuery = $this->Items->Apoios->find('list');
        $apoiosQuery->orderBy(['Apoios.numero_texto' => 'ASC']);
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

    /**
     * Apply the evento and relator-isolation conditions shared by the index
     * query and the TR-options query.
     *
     * @param \Cake\ORM\Query\SelectQuery $query The query to constrain.
     * @param mixed $selectedEventoId The active event id, if any.
     * @return \Cake\ORM\Query\SelectQuery
     */
    private function applyBaseConditions(SelectQuery $query, mixed $selectedEventoId): SelectQuery
    {
        if ($selectedEventoId) {
            $query->matching('Apoios', function ($q) use ($selectedEventoId) {
                return $q->where(['Apoios.evento_id' => $selectedEventoId]);
            });
        }
        $identity = $this->Authentication->getIdentity();
        if ($identity && $identity->role === 'relator') {
            $query->where(['OR' => [
                ['Items.item NOT LIKE' => '%.99'],
                ['Items.user_id' => $identity->id],
            ]]);
        }

        return $query;
    }

    /**
     * Validate the item's apoio/evento/tr/item-prefix consistency and set
     * errors on the entity when constraints are violated.
     *
     * @param \App\Model\Entity\Item $item The item entity being validated.
     * @return void
     */
    private function validateApoioConsistency(\App\Model\Entity\Item $item): void
    {
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
                $actualPrefix = substr((string)$item->item, 0, strlen($expectedPrefix));
                if ($actualPrefix !== $expectedPrefix) {
                    $item->setError('item', __('Os dois primeiros dígitos do item devem ser iguais a Apoios.numero_texto ({0}).', $expectedPrefix));
                }
            } else {
                $item->setError('apoio_id', __('Apoio não encontrado.'));
            }
        } else {
            $item->setError('apoio_id', __('Apoio é obrigatório.'));
        }
    }

    /**
     * Flash a list of the entity's validation errors, or a generic message
     * when no field errors are present.
     *
     * @param \App\Model\Entity\Item $item The item entity that failed to save.
     * @return void
     */
    private function flashEntityErrors(\App\Model\Entity\Item $item): void
    {
        $errors = [];
        if ($item->getErrors()) {
            foreach ($item->getErrors() as $fieldErrors) {
                foreach ($fieldErrors as $message) {
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
}
