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
     * @param string $location Calling location for the log.
     * @param string $message Log message.
     * @param array<string, mixed> $data Associated data.
     * @param string $hypothesisId Hypothesis identifier.
     */
    private function agentDebugLog(string $location, string $message, array $data, string $hypothesisId): void
    {
        // #region agent log
        @file_put_contents(
            '/home/luis/html/andestr5/.cursor/debug-bc0914.log',
            json_encode([
                'sessionId' => 'bc0914',
                'timestamp' => (int)(microtime(true) * 1000),
                'location' => $location,
                'message' => $message,
                'data' => $data,
                'hypothesisId' => $hypothesisId,
                'runId' => 'audit',
            ], JSON_UNESCAPED_UNICODE) . "\n",
            FILE_APPEND
        );
        // #endregion
    }

    /**
     * Index method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function index()
    {
        $this->Authorization->skipAuthorization();
        $query = $this->Votacoes->find()
            ->orderBy(['Votacoes.item' => 'ASC'])
            ->contain(['Users', 'Eventos', 'Items']);

        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        if ($selectedEventoId) {
            $query->where(['Votacoes.evento_id' => $selectedEventoId]);
        }

        $itemId = $this->request->getQuery('item_id');
        if ($itemId) {
            $query->where(['Votacoes.item_id' => $itemId]);
        }

        // TR filter - check GET param first, then session
        $session = $this->request->getSession();
        $trFilter = $this->request->getQuery('tr_filter');
        
        if ($trFilter !== null) {
            // Store in session when explicitly set via GET
            if ($trFilter === 'todos') {
                $session->delete('votacoes_tr_filter');
            } else {
                $session->write('votacoes_tr_filter', $trFilter);
            }
        } else {
            // Fall back to session value
            $trFilter = $session->read('votacoes_tr_filter');
        }
        
        if ($trFilter && $trFilter !== 'todos') {
            $query->where(['Votacoes.tr' => (int)$trFilter]);
        }

        $identity = $this->Authentication->getIdentity();
        if ($identity && $identity->role === 'relator') {
            $query->where(['Votacoes.grupo' => (int)substr((string)$identity->username, 5)]);
        }

        $votacoes = $this->paginate($query);

        // Get unique TR values for the filter dropdown
        $trValuesQuery = $this->Votacoes->find()
            ->select(['tr'])
            ->distinct('tr');

        if ($selectedEventoId) {
            $trValuesQuery->where(['Votacoes.evento_id' => $selectedEventoId]);
        }
        if ($identity && $identity->role === 'relator') {
            $trValuesQuery->where(['Votacoes.grupo' => (int)substr((string)$identity->username, 5)]);
        }
        $trValues = $trValuesQuery->orderBy(['Votacoes.tr' => 'ASC'])
            ->all()
            ->extract('tr')
            ->toArray();

        // #region agent log
        $this->agentDebugLog('VotacoesController.php:index', 'TR filter dropdown values', [
            'selectedEventoId' => $selectedEventoId,
            'trValuesCount' => count($trValues),
            'trValues' => $trValues,
        ], 'D');
        // #endregion

        $this->set(compact('votacoes', 'trValues', 'trFilter'));
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
            $identity = $this->Authentication->getIdentity();
            if (!$this->ensureRelatorCanAccessItem($identity, (int)$itemId, $selectedEventoId)) {
                return $this->redirect(['action' => 'index']);
            }

            try {
                $itemRecord = $this->Votacoes->Items->get($itemId);
                // #region agent log
                $this->agentDebugLog('VotacoesController.php:add', 'Relator .99 item access check', [
                    'itemId' => $itemId,
                    'itemCode' => $itemRecord->item,
                    'isInclusionItemCode' => $this->isInclusionItemCode((string)$itemRecord->item),
                    'itemUserId' => $itemRecord->user_id,
                    'identityId' => $identity?->id,
                ], 'C');
                // #endregion
                if ($identity && $identity->role === 'relator' && $this->isInclusionItemCode((string)$itemRecord->item) && (int)$itemRecord->user_id !== (int)$identity->id) {
                    throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to vote on this item.'));
                }
                $votacao->item_id = (int)$itemId;
                $votacao->item = $itemRecord->item;
            } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
                // Ignore if item not found
            }
        }

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $resultado = $data['resultado'] ?? '';
            
            $votacao = $this->Votacoes->patchEntity($votacao, $data);
            if ($selectedEventoId) {
                $votacao->evento_id = $selectedEventoId;
            }

            $identity = $this->Authentication->getIdentity();
            if ($identity && $identity->role === 'relator') {
                $votacao->user_id = $identity->id;
                $votacao->grupo = (int)substr((string)$identity->username, 5);
            }

            $applySuccess = true;
            if ($this->isInclusionResult($resultado)) {
                $applySuccess = $this->applyInclusionItem($votacao, $data, $selectedEventoId, $identity);
            }

            if ($applySuccess) {
                if (!$this->ensureRelatorCanAccessItem($identity, $votacao->item_id, $selectedEventoId)) {
                    return $this->redirect(['action' => 'index']);
                }

                if (empty($votacao->data)) {
                    $votacao->data = new \Cake\I18n\DateTime();
                }

                if ($this->Votacoes->save($votacao)) {
                    $this->Flash->success(__('The votacao has been saved.'));

                    return $this->redirect(['action' => 'index']);
                }
                
                $errors = [];
                if ($votacao->getErrors()) {
                    foreach ($votacao->getErrors() as $field => $fieldErrors) {
                        foreach ($fieldErrors as $rule => $message) {
                            $errors[] = $message;
                        }
                    }
                }
                if (!empty($errors)) {
                    $this->Flash->error(__('The votacao could not be saved: {0}', implode(', ', array_unique($errors))));
                } else {
                    $this->Flash->error(__('The votacao could not be saved. Please, try again.'));
                }
            }
        }
        $identity = $this->Authentication->getIdentity();
        // Get users with role and username for JavaScript
        $usersQuery = $this->Votacoes->Users->find()
            ->select(['id', 'username', 'role'])
            ->toArray();
        
        // Create a map for the dropdown (id => username)
        $users = [];
        $usersData = [];
        foreach ($usersQuery as $user) {
            $users[$user->id] = $user->username;
            $usersData[$user->id] = [
                'role' => $user->role,
                'username' => $user->username,
            ];
        }
        
        $eventos = $this->buildEventoOptions();
        $items = $this->buildItemOptions($selectedEventoId, $identity, $votacao->item_id ? (int)$votacao->item_id : null);
        $itemTexts = $this->buildItemTextMap($selectedEventoId, $identity, $votacao->item_id ? (int)$votacao->item_id : null);

        $this->set(compact('votacao', 'users', 'usersData', 'eventos', 'items', 'itemTexts'));
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
        $originalResultado = (string)$votacao->resultado;
        $originalGrupo = (int)$votacao->grupo;

        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            $resultado = $data['resultado'] ?? '';
            $votacao = $this->Votacoes->patchEntity($votacao, $data);
            if ($selectedEventoId) {
                $votacao->evento_id = $selectedEventoId;
            }

            $identity = $this->Authentication->getIdentity();
            if ($identity && $identity->role === 'relator') {
                $votacao->user_id = $identity->id;
                $votacao->grupo = (int)substr((string)$identity->username, 5);
            }

            $applySuccess = true;
            if ($this->isInclusionResult($resultado)) {
                $applySuccess = $this->applyInclusionItem($votacao, $data, $selectedEventoId, $identity, $originalResultado);
            }

            if ($applySuccess) {
                if (!$this->ensureRelatorCanAccessItem($identity, $votacao->item_id, $selectedEventoId, $originalGrupo)) {
                    return $this->redirect(['action' => 'index']);
                }

                if (empty($votacao->data)) {
                    $votacao->data = new \Cake\I18n\DateTime();
                }

                if ($this->Votacoes->save($votacao)) {
                    $this->Flash->success(__('The votacao has been saved.'));

                    return $this->redirect(['action' => 'index']);
                }
                
                $errors = [];
                if ($votacao->getErrors()) {
                    foreach ($votacao->getErrors() as $field => $fieldErrors) {
                        foreach ($fieldErrors as $rule => $message) {
                            $errors[] = $message;
                        }
                    }
                }
                if (!empty($errors)) {
                    $this->Flash->error(__('The votacao could not be saved: {0}', implode(', ', array_unique($errors))));
                } else {
                    $this->Flash->error(__('The votacao could not be saved. Please, try again.'));
                }
            }
        }
        $identity = $this->Authentication->getIdentity();
        $users = $this->Votacoes->Users->find('list', limit: 50)->all();
        $eventos = $this->buildEventoOptions();
        $items = $this->buildItemOptions($selectedEventoId, $identity, $votacao->item_id ? (int)$votacao->item_id : null);
        $itemTexts = $this->buildItemTextMap($selectedEventoId, $identity, $votacao->item_id ? (int)$votacao->item_id : null);

        $this->set(compact('votacao', 'users', 'eventos', 'items', 'itemTexts'));
    }

    /**
     * Build event options with both ID and name visible in the form.
     *
     * @return array<int, string>
     */
    private function buildEventoOptions(): array
    {
        $options = [];
        $eventos = $this->Votacoes->Eventos->find()
            ->select(['id', 'nome'])
            ->orderBy(['Eventos.id' => 'ASC'])
            ->all();

        foreach ($eventos as $evento) {
            $options[(int)$evento->id] = sprintf('%d - %s', $evento->id, $evento->nome);
        }

        return $options;
    }

    /**
     * @param string $resultado Vote result value from the request.
     * @return bool
     */
    private function isInclusionResult(string $resultado): bool
    {
        return in_array($resultado, ['inclusao', 'inclusão'], true);
    }

    /**
     * @param array<string, mixed> $data Request data.
     * @param int|string|null $selectedEventoId Event selected in session.
     * @param mixed $identity Authenticated identity.
     * @param string|null $originalResultado Previous persisted result on edit.
     * @return bool
     */
    private function applyInclusionItem(
        object $votacao,
        array $data,
        $selectedEventoId,
        $identity,
        ?string $originalResultado = null
    ): bool {
        $tr = (int)($data['tr'] ?? 0);
        $apoiosTable = $this->getTableLocator()->get('Apoios');
        $apoio = $apoiosTable->find()
            ->select(['id'])
            ->where(['evento_id' => $selectedEventoId, 'numero_texto' => $tr])
            ->first();

        if (!$apoio) {
            $this->Flash->error(__('Erro ao localizar apoio_id. Tente novamente.'));

            return false;
        }

        $itemCode = $this->buildInclusionItemCode($tr);
        // #region agent log
        $this->agentDebugLog('VotacoesController.php:applyInclusionItem', 'Inclusion item code generated', [
            'tr' => $tr,
            'itemCode' => $itemCode,
            'source' => 'buildInclusionItemCode',
        ], 'A');
        // #endregion
        $itemTexto = (string)($data['item_modificada'] ?? '');
        $linkedItem = null;

        if (!empty($votacao->item_id)) {
            $linkedItem = $this->Votacoes->Items->find()
                ->where(['Items.id' => $votacao->item_id])
                ->first();
        }

        $canReuseExistingItem = $linkedItem && (
            $originalResultado === null
                ? str_ends_with((string)$linkedItem->item, '.99')
                : ($this->isInclusionResult($originalResultado) || str_ends_with((string)$linkedItem->item, '.99'))
        );

        if ($canReuseExistingItem) {
            $linkedItem->apoio_id = $apoio->id;
            $linkedItem->item = $itemCode;
            $linkedItem->texto = $itemTexto;
            $linkedItem->tr = $tr;
            if (!$this->Votacoes->Items->save($linkedItem)) {
                $this->Flash->error(__('Erro ao atualizar o item de inclusão. Tente novamente.'));

                return false;
            }
        } else {
            $novoItem = $this->Votacoes->Items->newEmptyEntity();
            $novoItem->apoio_id = $apoio->id;
            $novoItem->item = $itemCode;
            $novoItem->texto = $itemTexto;
            $novoItem->tr = $tr;
            $novoItem->user_id = $identity->id;
            if (!$this->Votacoes->Items->save($novoItem)) {
                $this->Flash->error(__('Erro ao criar novo item. Tente novamente.'));

                return false;
            }
            $linkedItem = $novoItem;
        }

        $votacao->item_id = $linkedItem->id;
        $votacao->item = $itemCode;
        $votacao->resultado = 'inclusão';
        $votacao->item_modificada = $itemTexto;

        return true;
    }

    /**
     * @param int $tr TR number.
     * @return string
     */
    private function buildInclusionItemCode(int $tr): string
    {
        return sprintf('%02d.99', $tr);
    }

    /**
     * @param string $itemCode Item code from the database.
     * @return bool
     */
    private function isInclusionItemCode(string $itemCode): bool
    {
        return str_ends_with($itemCode, '.99');
    }

    /**
     * @param mixed $identity Authenticated identity.
     * @param int|string|null $itemId Selected item id.
     * @param int|string|null $selectedEventoId Event selected in session.
     * @param int|null $votacaoGrupo Existing vote group when editing.
     * @return bool
     */
    private function ensureRelatorCanAccessItem($identity, $itemId, $selectedEventoId, ?int $votacaoGrupo = null): bool
    {
        if (!$itemId) {
            return true;
        }

        try {
            $itemRecord = $this->Votacoes->Items->get($itemId, contain: ['Apoios']);
            $itemEventoId = $itemRecord->apoio->evento_id ?? null;
            if ($selectedEventoId && (!$itemEventoId || (int)$itemEventoId !== (int)$selectedEventoId)) {
                $this->Flash->error(__('The selected item does not belong to the active event.'));

                return false;
            }

            if (!$identity || $identity->role !== 'relator') {
                return true;
            }

            $identityGrupo = (int)substr((string)$identity->username, 5);
            if ($votacaoGrupo !== null && $identityGrupo !== $votacaoGrupo) {
                $this->Flash->error(__('You are not authorized to edit this vote.'));

                return false;
            }

            if ($this->isInclusionItemCode((string)$itemRecord->item) && (int)$itemRecord->user_id !== (int)$identity->id) {
                throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to vote on this item.'));
            }
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->Flash->error(__('Invalid item selected.'));

            return false;
        }

        return true;
    }

    /**
     * Build item options with both ID and item code visible in the form.
     *
     * @param int|string|null $selectedEventoId Selected event from session.
     * @param mixed $identity Authenticated identity.
     * @param int|null $currentItemId Current item on edit forms.
     * @return array<int, string>
     */
    private function buildItemOptions($selectedEventoId, $identity, ?int $currentItemId = null): array
    {
        $query = $this->buildSelectableItemsQuery($selectedEventoId, $identity)
            ->select(['Items.id', 'Items.item'])
            ->orderBy(['Items.id' => 'ASC']);

        $options = [];
        foreach ($query->all() as $item) {
            $options[(int)$item->id] = sprintf('%d - %s', $item->id, $item->item);
        }

        if ($currentItemId && !isset($options[$currentItemId])) {
            $item = $this->Votacoes->Items->find()
                ->select(['Items.id', 'Items.item'])
                ->where(['Items.id' => $currentItemId])
                ->first();

            if ($item) {
                $options[(int)$item->id] = sprintf('%d - %s', $item->id, $item->item);
                asort($options);
            }
        }

        return $options;
    }

    /**
     * Build item text map limited to the same items exposed in the form.
     *
     * @param int|string|null $selectedEventoId Selected event from session.
     * @param mixed $identity Authenticated identity.
     * @param int|null $currentItemId Current item on edit forms.
     * @return array<int, string>
     */
    private function buildItemTextMap($selectedEventoId, $identity, ?int $currentItemId = null): array
    {
        $query = $this->buildSelectableItemsQuery($selectedEventoId, $identity)
            ->select(['Items.id', 'Items.texto'])
            ->orderBy(['Items.id' => 'ASC']);

        $itemTexts = [];
        foreach ($query->all() as $item) {
            $itemTexts[(int)$item->id] = (string)$item->texto;
        }

        if ($currentItemId && !isset($itemTexts[$currentItemId])) {
            $item = $this->Votacoes->Items->find()
                ->select(['Items.id', 'Items.texto'])
                ->where(['Items.id' => $currentItemId])
                ->first();

            if ($item) {
                $itemTexts[(int)$item->id] = (string)$item->texto;
            }
        }

        return $itemTexts;
    }

    /**
     * Build the base query used to populate vote item form data.
     *
     * @param int|string|null $selectedEventoId Selected event from session.
     * @param mixed $identity Authenticated identity.
     * @return \Cake\ORM\Query\SelectQuery
     */
    private function buildSelectableItemsQuery($selectedEventoId, $identity)
    {
        $query = $this->Votacoes->Items->find();

        if ($selectedEventoId) {
            $query->innerJoinWith('Apoios')
                ->where(['Apoios.evento_id' => $selectedEventoId]);
        }

        if ($identity && $identity->role === 'relator') {
            $query->where([
                'OR' => [
                    ['Items.item NOT LIKE' => '%.99'],
                    ['Items.user_id' => $identity->id],
                ],
            ]);
        }

        return $query;
    }

    /**
     * Find the existing vote for the same event/group/item combination.
     *
     * @param int $eventoId Event id.
     * @param int $grupo Group number.
     * @param int $itemId Item id.
     * @return \App\Model\Entity\Votacao|null
     */
    private function findExistingVoteForGroupItem(int $eventoId, int $grupo, int $itemId): ?object
    {
        return $this->Votacoes->find()
            ->where([
                'Votacoes.evento_id' => $eventoId,
                'Votacoes.grupo' => $grupo,
                'Votacoes.item_id' => $itemId,
            ])
            ->first();
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
     * Fase 1 — Votação da TR inteira (rejeição opcional).
     *
     * @param string|null $grupo Grupo number.
     * @param string|null $tr TR number.
     * @return \Cake\Http\Response|null|void
     */
    public function votarTr($grupo = null, $tr = null)
    {
        $this->Authorization->skipAuthorization();

        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        $identity = $this->Authentication->getIdentity();

        $grupo = (int)$grupo;
        $tr = (int)$tr;

        if (!$grupo || !$tr || !$selectedEventoId) {
            $this->Flash->error(__('Parâmetros inválidos.'));
            return $this->redirect(['action' => 'index']);
        }

        if (!$identity || $identity->role !== 'relator') {
            $this->Flash->error(__('Acesso negado.'));
            return $this->redirect(['action' => 'index']);
        }

        $userGrupo = (int)substr((string)$identity->username, 5);
        if ($grupo !== $userGrupo) {
            $this->Flash->error(__('Acesso negado ao grupo {0}.', $grupo));
            return $this->redirect(['action' => 'index']);
        }

        // Buscar itens desta TR no evento ativo
        $itensTr = $this->Votacoes->Items->find()
            ->innerJoinWith('Apoios')
            ->where([
                'Apoios.evento_id' => $selectedEventoId,
                'Items.tr' => $tr,
            ])
            ->orderBy(['Items.item' => 'ASC'])
            ->all();

        if ($itensTr->isEmpty()) {
            $this->Flash->error(__('Nenhum item encontrado para esta TR.'));
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $resultado = $data['resultado'] ?? '';

            if ($resultado === 'suprimida') {
                $existingVoteItemIds = $this->Votacoes->find()
                    ->select(['item_id'])
                    ->distinct()
                    ->where([
                        'Votacoes.evento_id' => $selectedEventoId,
                        'Votacoes.grupo' => $grupo,
                        'Votacoes.tr' => $tr,
                    ])
                    ->all()
                    ->extract('item_id')
                    ->map(fn ($value) => (int)$value)
                    ->toList();

                $entities = [];
                foreach ($itensTr as $item) {
                    if (in_array((int)$item->id, $existingVoteItemIds, true)) {
                        continue;
                    }

                    $votacao = $this->Votacoes->newEmptyEntity();
                    $votacao->user_id = $identity->id;
                    $votacao->evento_id = $selectedEventoId;
                    $votacao->grupo = $grupo;
                    $votacao->tr = $tr;
                    $votacao->item_id = $item->id;
                    $votacao->item = $item->item;
                    $votacao->resultado = 'suprimida';
                    $votacao->votacao = $data['votacao'] ?? '';
                    $votacao->item_modificada = '';
                    $votacao->data = new \Cake\I18n\DateTime();
                    $votacao->observacoes = $data['observacoes'] ?? '';
                    $votacao->destaque_minoria = false;
                    $entities[] = $votacao;
                }

                if (empty($entities)) {
                    $this->Flash->success(__('Todos os itens da TR {0} já possuem votação registrada para o grupo {1}.', $tr, $grupo));

                    return $this->redirect(['action' => 'index']);
                }

                if ($this->Votacoes->saveMany($entities)) {
                    $this->Flash->success(__('Votação de supresão da TR {0} registrada em {1} itens.', $tr, count($entities)));
                    return $this->redirect(['action' => 'index']);
                }
                
                $errors = [];
                foreach ($entities as $entity) {
                    if ($entity->getErrors()) {
                        foreach ($entity->getErrors() as $field => $fieldErrors) {
                            foreach ($fieldErrors as $rule => $message) {
                                $errors[] = $message;
                            }
                        }
                    }
                }
                if (!empty($errors)) {
                    $this->Flash->error(__('Erro ao salvar a votação da TR: {0}', implode(', ', array_unique($errors))));
                } else {
                    $this->Flash->error(__('Erro ao salvar a votação da TR. Tente novamente.'));
                }
            } else {
                // TR não suprimida — nada registrado
                $this->Flash->success(__('TR {0} aprovada. Prossiga para votação dos itens em discussão.', $tr));
                return $this->redirect(['action' => 'index']);
            }
        }

        $this->set(compact('itensTr', 'grupo', 'tr'));
    }

    /**
     * Fase 2 — Votação individual de item em discussão.
     *
     * @param string|null $itemId Item id.
     * @return \Cake\Http\Response|null|void
     */
    public function votarItem($itemId = null)
    {
        $this->Authorization->skipAuthorization();

        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        $identity = $this->Authentication->getIdentity();

        if (!$identity || $identity->role !== 'relator') {
            $this->Flash->error(__('Acesso negado.'));
            return $this->redirect(['action' => 'index']);
        }

        $itemId = (int)$itemId;
        if (!$itemId || !$selectedEventoId) {
            $this->Flash->error(__('Parâmetros inválidos.'));
            return $this->redirect(['action' => 'index']);
        }

        if (!$this->ensureRelatorCanAccessItem($identity, $itemId, $selectedEventoId)) {
            return $this->redirect(['action' => 'index']);
        }

        try {
            $item = $this->Votacoes->Items->get($itemId, contain: ['Apoios']);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->Flash->error(__('Item não encontrado.'));
            return $this->redirect(['action' => 'index']);
        }

        // Verify item belongs to the selected event
        $itemEventoId = $item->apoio->evento_id ?? null;
        if (!$itemEventoId || (int)$itemEventoId !== (int)$selectedEventoId) {
            $this->Flash->error(__('Item não pertence ao evento ativo.'));
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            $existingVote = $this->findExistingVoteForGroupItem(
                (int)$selectedEventoId,
                (int)substr((string)$identity->username, 5),
                (int)$item->id
            );
            if ($existingVote) {
                $this->Flash->error(__('Já existe votação registrada para o item {0} no grupo {1}.', $item->item, $existingVote->grupo));

                return $this->redirect(['action' => 'edit', $existingVote->id]);
            }
            
            $votacao = $this->Votacoes->newEmptyEntity();
            $votacao = $this->Votacoes->patchEntity($votacao, $data);
            $votacao->user_id = $identity->id;
            $votacao->evento_id = $selectedEventoId;
            $votacao->grupo = (int)substr((string)$identity->username, 5);
            $votacao->tr = $item->tr;
            $votacao->item_id = $item->id;
            $votacao->item = $item->item;
            $votacao->item_modificada = $data['item_modificada'] ?? '';
            $votacao->destaque_minoria = !empty($data['destaque_minoria']);
            $votacao->data = new \Cake\I18n\DateTime();

            if ($this->Votacoes->save($votacao)) {
                $this->Flash->success(__('Votação do item {0} registrada.', $item->item));
                return $this->redirect(['action' => 'index']);
            }
            
            $errors = [];
            if ($votacao->getErrors()) {
                foreach ($votacao->getErrors() as $field => $fieldErrors) {
                    foreach ($fieldErrors as $rule => $message) {
                        $errors[] = $message;
                    }
                }
            }
            if (!empty($errors)) {
                $this->Flash->error(__('Erro ao salvar a votação do item: {0}', implode(', ', array_unique($errors))));
            } else {
                $this->Flash->error(__('Erro ao salvar a votação do item. Tente novamente.'));
            }
        } else {
            $votacao = $this->Votacoes->newEmptyEntity();
        }

        $this->set(compact('item', 'votacao'));
    }

    /**
     * Fase 3 — Aprovação em bloco dos itens não discutidos.
     *
     * @param string|null $grupo Grupo number.
     * @param string|null $tr TR number.
     * @return \Cake\Http\Response|null|void
     */
    public function votarRestantes($grupo = null, $tr = null)
    {
        $this->Authorization->skipAuthorization();

        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        $identity = $this->Authentication->getIdentity();

        $grupo = (int)$grupo;
        $tr = (int)$tr;

        if (!$grupo || !$tr || !$selectedEventoId) {
            $this->Flash->error(__('Parâmetros inválidos.'));
            return $this->redirect(['action' => 'index']);
        }

        if (!$identity || $identity->role !== 'relator') {
            $this->Flash->error(__('Acesso negado.'));
            return $this->redirect(['action' => 'index']);
        }

        $userGrupo = (int)substr((string)$identity->username, 5);
        if ($grupo !== $userGrupo) {
            $this->Flash->error(__('Acesso negado ao grupo {0}.', $grupo));
            return $this->redirect(['action' => 'index']);
        }

        // Usar o finder personalizado
        $itensRestantes = $this->Votacoes->findItensSemVoto(
            $this->Votacoes->Items->find(),
            ['grupo' => $grupo, 'tr' => $tr, 'evento_id' => $selectedEventoId, 'user_id' => $identity->id]
        )->all();

        if ($itensRestantes->isEmpty()) {
            $this->Flash->success(__('Todos os itens da TR {0} já possuem votação registrada.', $tr));
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            $entities = [];
            foreach ($itensRestantes as $item) {
                $votacao = $this->Votacoes->newEmptyEntity();
                $votacao->user_id = $identity->id;
                $votacao->evento_id = $selectedEventoId;
                $votacao->grupo = $grupo;
                $votacao->tr = $tr;
                $votacao->item_id = $item->id;
                $votacao->item = $item->item;
                $votacao->resultado = 'aprovada';
                $votacao->votacao = $data['votacao'] ?? '';
                $votacao->item_modificada = '';
                $votacao->data = new \Cake\I18n\DateTime();
                $votacao->observacoes = $data['observacoes'] ?? '';
                $votacao->destaque_minoria = false;
                $entities[] = $votacao;
            }

            if ($this->Votacoes->saveMany($entities)) {
                $this->Flash->success(__('Votação afirmativa registrada em {0} itens da TR {1}.', count($entities), $tr));
                return $this->redirect(['action' => 'index']);
            }
            
            $errors = [];
            foreach ($entities as $entity) {
                if ($entity->getErrors()) {
                    foreach ($entity->getErrors() as $field => $fieldErrors) {
                        foreach ($fieldErrors as $rule => $message) {
                            $errors[] = $message;
                        }
                    }
                }
            }
            if (!empty($errors)) {
                $this->Flash->error(__('Erro ao salvar a votação dos itens restantes: {0}', implode(', ', array_unique($errors))));
            } else {
                $this->Flash->error(__('Erro ao salvar a votação dos itens restantes. Tente novamente.'));
            }
        }

        $this->set(compact('itensRestantes', 'grupo', 'tr'));
    }

    /**
     * Fase 4 — Inserir novo item durante a votação.
     *
     * @param string|null $grupo Grupo number.
     * @param string|null $tr TR number.
     * @return \Cake\Http\Response|null|void
     */
    public function inserirItem($grupo = null, $tr = null)
    {
        $this->Authorization->skipAuthorization();

        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        $identity = $this->Authentication->getIdentity();

        $grupo = (int)$grupo;
        $tr = (int)$tr;

        if (!$grupo || !$tr || !$selectedEventoId) {
            $this->Flash->error(__('Parâmetros inválidos.'));
            return $this->redirect(['action' => 'index']);
        }

        if (!$identity || $identity->role !== 'relator') {
            $this->Flash->error(__('Acesso negado.'));
            return $this->redirect(['action' => 'index']);
        }

        $userGrupo = (int)substr((string)$identity->username, 5);
        if ($grupo !== $userGrupo) {
            $this->Flash->error(__('Acesso negado ao grupo {0}.', $grupo));
            return $this->redirect(['action' => 'index']);
        }

        // Encontrar o Apoio que fundamenta esta TR
        $apoioItem = $this->Votacoes->Items->find()
            ->innerJoinWith('Apoios')
            ->where([
                'Apoios.evento_id' => $selectedEventoId,
                'Items.tr' => $tr,
            ])
            ->contain(['Apoios'])
            ->first();

        if (!$apoioItem) {
            $this->Flash->error(__('TR {0} não encontrada neste evento.', $tr));
            return $this->redirect(['action' => 'index']);
        }

        $apoioId = $apoioItem->apoio_id;

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            if (!isset($data['item_modificada'])) {
                $data['item_modificada'] = '';
            }

            // Criar o novo Item
            $itemCode = $this->buildInclusionItemCode($tr);
            $novoItem = $this->Votacoes->Items->newEntity([
                'apoio_id' => $apoioId,
                'tr' => $tr,
                'item' => $itemCode,
                'texto' => $data['texto'] ?? '',
                'user_id' => $identity->id,
            ]);

            if (!$this->Votacoes->Items->save($novoItem)) {
                $this->Flash->error(__('Erro ao criar o novo item.'));
                return $this->redirect(['action' => 'index']);
            }

            // Criar a Votacao para o novo item
            $votacao = $this->Votacoes->newEmptyEntity();
            $votacao = $this->Votacoes->patchEntity($votacao, $data);
            $votacao->user_id = $identity->id;
            $votacao->evento_id = $selectedEventoId;
            $votacao->grupo = $grupo;
            $votacao->tr = $tr;
            $votacao->item_id = $novoItem->id;
            $votacao->item = $novoItem->item;
            $votacao->resultado = 'inclusão';
            $votacao->item_modificada = $data['item_modificada'] ?? '';
            $votacao->destaque_minoria = !empty($data['destaque_minoria']);
            $votacao->data = new \Cake\I18n\DateTime();

            if ($this->Votacoes->save($votacao)) {
                $this->Flash->success(__('Novo item {0} inserido e votação registrada.', $itemCode));
                return $this->redirect(['action' => 'index']);
            }

            // Rollback: remover o item criado (only if it was saved successfully)
            if ($novoItem->id) {
                $this->Votacoes->Items->delete($novoItem);
            }
            
            $errors = [];
            if ($votacao->getErrors()) {
                foreach ($votacao->getErrors() as $field => $fieldErrors) {
                    foreach ($fieldErrors as $rule => $message) {
                        $errors[] = $message;
                    }
                }
            }
            if (!empty($errors)) {
                $this->Flash->error(__('Erro ao salvar a votação do novo item: {0}', implode(', ', array_unique($errors))));
            } else {
                $this->Flash->error(__('Erro ao salvar a votação do novo item. Tente novamente.'));
            }
        } else {
            $votacao = $this->Votacoes->newEmptyEntity();
        }

        $itemCode = $this->buildInclusionItemCode($tr);
        $this->set(compact('grupo', 'tr', 'itemCode', 'apoioId', 'apoioItem', 'votacao'));
    }

    /**
     * Relatório method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function relatorio()
    {
        $this->Authorization->skipAuthorization();
        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        $identity = $this->Authentication->getIdentity();
        $trInput = $this->request->getQuery('trs');
        $trList = [];
        $userGrupo = null;
        if ($identity && $identity->role === 'relator') {
            $userGrupo = (int)substr((string)$identity->username, 5);
        }
        if ($trInput !== null && trim($trInput) !== '') {
            $parts = explode(',', $trInput);
            foreach ($parts as $part) {
                $val = trim($part);
                if (is_numeric($val)) {
                    $trList[] = (int)$val;
                }
            }
        }
        
        if (!empty($trList) && $selectedEventoId) {
            $whereConditions = [
                'Votacoes.evento_id' => $selectedEventoId,
                'Votacoes.tr IN' => $trList,
            ];
            
            // Only filter by grupo if user is relator
            if ($userGrupo !== null) {
                $whereConditions['Votacoes.grupo'] = $userGrupo;
            }
            
            // First, get the votacoes with their items
            $votacoes = $this->Votacoes->find()
                ->contain([
                    'Users' => ['fields' => ['username']], 
                    'Items' => ['fields' => ['texto', 'item', 'tr']]
                ])
                ->where($whereConditions)
                ->order(['Votacoes.tr' => 'ASC', 'Votacoes.item' => 'ASC'])
                ->all();
            
            // Retain original queried $trList so template warnings can display correctly
            
            $download = $this->request->getQuery('download');
            if ($download === 'markdown') {
                $evento = $this->Votacoes->Eventos->find()->where(['id' => $selectedEventoId])->first();
                
                $markdownContent = "# Relatório de Votações por TR\n\n";
                if ($evento) {
                    $markdownContent = sprintf(
                        "# %s\n- **Data:** %s\n- **Local:** %s\n",
                        $evento->nome,
                        $evento->data,
                        $evento->local
                    );
                }
                if ($identity && $identity->role === 'relator') {
                    $markdownContent .= sprintf("- **Relator:** %s\n- **Grupo:** G%d\n", $identity->username, $userGrupo);
                }
                $markdownContent .= sprintf("- **TRs Consultadas:** %s\n\n", implode(', ', $trList));
                $markdownContent .= "---\n\n";
                
                $groupedItems = [];
                foreach ($votacoes as $votacao) {
                    $tr = (int)$votacao->tr;
                    $itemKey = (string)($votacao->item_id ?: $votacao->item ?: uniqid('item_', true));

                    if (!isset($groupedItems[$tr][$itemKey])) {
                        $groupedItems[$tr][$itemKey] = [
                            'codigo' => $votacao->item ?: ($votacao->votacao_item->item ?? ''),
                            'texto' => $votacao->votacao_item->texto ?? '',
                            'isAdded' => !empty($votacao->item) && str_ends_with($votacao->item, '99'),
                            'votes' => [],
                        ];
                    }

                    $groupedItems[$tr][$itemKey]['votes'][] = $votacao;
                }
                
                foreach ($trList as $trNum) {
                    $markdownContent .= "## TR " . $trNum . "\n\n";
                    if (empty($groupedItems[$trNum])) {
                        $markdownContent .= "Nenhuma votação registrada para a TR " . $trNum . " no evento ativo.\n\n";
                    } else {
                        foreach ($groupedItems[$trNum] as $itemData) {
                            $hasInclusaoVote = false;
                            $inclusaoTexto = $itemData['texto'];
                            foreach ($itemData['votes'] as $vote) {
                                if (strtolower((string)$vote->resultado) === 'inclusão' || strtolower((string)$vote->resultado) === 'inclusao') {
                                    $hasInclusaoVote = true;
                                    if (!empty($vote->item_modificada)) {
                                        $inclusaoTexto = $vote->item_modificada;
                                    }
                                    break;
                                }
                            }
                            
                            $itemTitle = "### Item " . $itemData['codigo'];
                            if ($itemData['isAdded']) {
                                $itemTitle .= " (Item Adicionado)";
                            }
                            $markdownContent .= $itemTitle . "\n\n";
                            
                            if (!$hasInclusaoVote && !empty($itemData['texto'])) {
                                $markdownContent .= "> " . str_replace("\n", "\n> ", trim((string)$itemData['texto'])) . "\n\n";
                            }
                            
                            $markdownContent .= "| Grupo | Voto | Resultado | Relator |\n";
                            $markdownContent .= "| :--- | :--- | :--- | :--- |\n";
                            foreach ($itemData['votes'] as $vote) {
                                $resultadoStr = $vote->resultado;
                                if ($vote->destaque_minoria) {
                                    $resultadoStr .= " (⚠ Destaque de Minoria)";
                                }
                                $markdownContent .= sprintf(
                                    "| G%d | %s | %s | %s |\n",
                                    $vote->grupo,
                                    $vote->votacao,
                                    $resultadoStr,
                                    $vote->user->username
                                );
                            }
                            $markdownContent .= "\n";
                            
                            if (!$hasInclusaoVote) {
                                foreach ($itemData['votes'] as $vote) {
                                    if (!empty($vote->item_modificada)) {
                                        $markdownContent .= "**Modificação Proposta (G" . $vote->grupo . "):**\n";
                                        $markdownContent .= "> " . str_replace("\n", "\n> ", trim((string)$vote->item_modificada)) . "\n\n";
                                    }
                                }
                            }
                            
                            if ($hasInclusaoVote && !empty($inclusaoTexto)) {
                                $markdownContent .= "**Texto de Inclusão:**\n";
                                $markdownContent .= "> " . str_replace("\n", "\n> ", trim((string)$inclusaoTexto)) . "\n\n";
                            }
                        }
                    }
                }
                
                $response = $this->response;
                return $response->withType('text/markdown')
                    ->withHeader('Content-Disposition', 'attachment; filename="relatorio.md"')
                    ->withStringBody($markdownContent);
            }
            
            $this->set(compact('votacoes', 'trInput', 'trList'));
        } else {
            $votacoes = collection([]);
            $this->set(compact('votacoes', 'trInput', 'trList'));
        }
    }
}
