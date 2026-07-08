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
            ->where(['Votacoes.evento_id' => $selectedEventoId])
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
            try {
                $itemRecord = $this->Votacoes->Items->get($itemId);
                $identity = $this->Authentication->getIdentity();
                if ($identity && $identity->role === 'relator' && str_ends_with($itemRecord->item, '99') && (int)$itemRecord->user_id !== (int)$identity->id) {
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

            // Handle 'inclusao' - create new item first
            if ($resultado === 'inclusao') {
                // Locate the apoio_id of this item (if any) and set it in the new item's apoio_id field
                $apoioId = $this->fetchTable('Apoios')->find()
                    ->select(['id'])
                    ->where(['evento_id' => $selectedEventoId, 'numero_texto' => $data['tr']])
                    ->first();
                if (!$apoioId) {
                    $this->Flash->error(__('Erro ao localizar apoio_id. Tente novamente.'));
                    return;
                }
                $novoItem = $this->Votacoes->Items->newEmptyEntity();
                $novoItem->apoio_id = $apoioId ? $apoioId->id : null;
                $novoItem->item = $data['tr'] . '.' . '99';
                $novoItem->texto = $data['item_modificada'] ?? '';
                $novoItem->tr = $data['tr'] ?? '';
                $novoItem->user_id = $identity->id;
                if ($this->Votacoes->Items->save($novoItem)) {
                    // Use the new item ID for the votacao
                    $votacao->item_id = $novoItem->id;
                    $votacao->item = $novoItem->item;
                    $votacao->resultado = 'inclusão';
                    $votacao->item_modificada = $data['item_modificada'] ?? '';
                } else {
                    $this->Flash->error(__('Erro ao criar novo item. Tente novamente.'));
                    $this->set(compact('votacao', 'users', 'usersData', 'eventos', 'items'));
                    return;
                }
            }

            // Check item access
            if ($identity && $identity->role === 'relator' && $votacao->item_id) {
                try {
                    $itemRecord = $this->Votacoes->Items->get($votacao->item_id);
                    if (str_ends_with($itemRecord->item, '99') && (int)$itemRecord->user_id !== (int)$identity->id) {
                        throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to vote on this item.'));
                    }
                } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
                    $this->Flash->error(__('Invalid item selected.'));
                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($this->Votacoes->save($votacao)) {
                $this->Flash->success(__('The votacao has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The votacao could not be saved. Please, try again.'));
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
        
        // Get item texts for JavaScript auto-fill
        $itemTexts = [];
        $itemsQuery = $this->Votacoes->Items->find()
            ->select(['id', 'texto'])
            ->toArray();
        foreach ($itemsQuery as $item) {
            $itemTexts[$item->id] = $item->texto;
        }

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

        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');

        if ($this->request->is(['patch', 'post', 'put'])) {
            $votacao = $this->Votacoes->patchEntity($votacao, $this->request->getData());
            if ($selectedEventoId) {
                $votacao->evento_id = $selectedEventoId;
            }

            $identity = $this->Authentication->getIdentity();
            if ($identity && $identity->role === 'relator') {
                $votacao->user_id = $identity->id;
            }

            // Check item access
            if ($identity && $identity->role === 'relator' && $votacao->item_id) {
                try {
                    $itemRecord = $this->Votacoes->Items->get($votacao->item_id);
                    if (str_ends_with($itemRecord->item, '99') && (int)$itemRecord->user_id !== (int)$identity->id) {
                        throw new \Cake\Http\Exception\ForbiddenException(__('You are not authorized to vote on this item.'));
                    }
                } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
                    $this->Flash->error(__('Invalid item selected.'));
                    return $this->redirect(['action' => 'index']);
                }
            }

            if ($this->Votacoes->save($votacao)) {
                $this->Flash->success(__('The votacao has been saved.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('The votacao could not be saved. Please, try again.'));
        }
        $identity = $this->Authentication->getIdentity();
        $users = $this->Votacoes->Users->find('list', limit: 50)->all();
        $eventos = $this->buildEventoOptions();
        $items = $this->buildItemOptions($selectedEventoId, $identity, $votacao->item_id ? (int)$votacao->item_id : null);
        
        // Get item texts for JavaScript auto-fill
        $itemTexts = [];
        $itemsQuery = $this->Votacoes->Items->find()
            ->select(['id', 'texto'])
            ->toArray();
        foreach ($itemsQuery as $item) {
            $itemTexts[$item->id] = $item->texto;
        }

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
     * Build item options with both ID and item code visible in the form.
     *
     * @param int|string|null $selectedEventoId Selected event from session.
     * @param mixed $identity Authenticated identity.
     * @param int|null $currentItemId Current item on edit forms.
     * @return array<int, string>
     */
    private function buildItemOptions($selectedEventoId, $identity, ?int $currentItemId = null): array
    {
        $query = $this->Votacoes->Items->find()
            ->select(['Items.id', 'Items.item'])
            ->orderBy(['Items.id' => 'ASC']);

        if ($selectedEventoId) {
            $query->innerJoinWith('Apoios')
                ->where(['Apoios.evento_id' => $selectedEventoId]);
        }

        if ($identity && $identity->role === 'relator') {
            $query->where(['OR' => [
                ['Items.item NOT LIKE' => '%99'],
                ['Items.user_id' => $identity->id],
            ]]);
        }

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

        if ($identity->role !== 'relator') {
            $this->Flash->error(__('Acesso negado.'));
            return $this->redirect(['action' => 'index']);
        }

        $userGrupo = (int)substr($identity->username, 5);
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
                $entities = [];
                foreach ($itensTr as $item) {
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

                if ($this->Votacoes->saveMany($entities)) {
                    $this->Flash->success(__('Votação de rejeição da TR {0} registrada em {1} itens.', $tr, count($entities)));
                    return $this->redirect(['action' => 'index']);
                }
                $this->Flash->error(__('Erro ao salvar a votação da TR. Tente novamente.'));
            } else {
                // TR não rejeitada — nada registrado
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

        $itemId = (int)$itemId;
        if (!$itemId || !$selectedEventoId) {
            $this->Flash->error(__('Parâmetros inválidos.'));
            return $this->redirect(['action' => 'index']);
        }

        try {
            $item = $this->Votacoes->Items->get($itemId, contain: ['Apoios']);
        } catch (\Cake\Datasource\Exception\RecordNotFoundException $e) {
            $this->Flash->error(__('Item não encontrado.'));
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is('post')) {
            $data = $this->request->getData();

            if ($identity->role !== 'relator') {
                $this->Flash->error(__('Acesso negado.'));
                return $this->redirect(['action' => 'index']);
            }
            
            $votacao = $this->Votacoes->newEmptyEntity();
            $votacao = $this->Votacoes->patchEntity($votacao, $data);
            $votacao->user_id = $identity->id;
            $votacao->evento_id = $selectedEventoId;
            $votacao->grupo = (int)substr($identity->username, 5);
            $votacao->tr = $item->tr;
            $votacao->item_id = $item->id;
            $votacao->item = $item->item;
            $votacao->data = new \Cake\I18n\DateTime();

            if ($this->Votacoes->save($votacao)) {
                $this->Flash->success(__('Votação do item {0} registrada.', $item->item));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Erro ao salvar a votação do item. Tente novamente.'));
        }

        // Calcular minoria para destaque no GET
        $destaqueCalculado = false;
        $this->set(compact('item', 'destaqueCalculado'));
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

        if ($identity->role !== 'relator') {
            $this->Flash->error(__('Acesso negado.'));
            return $this->redirect(['action' => 'index']);
        }

        $userGrupo = (int)substr($identity->username, 5);
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
            $this->Flash->error(__('Erro ao salvar a votação dos itens restantes. Tente novamente.'));
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

        if ($identity->role !== 'relator') {
            $this->Flash->error(__('Acesso negado.'));
            return $this->redirect(['action' => 'index']);
        }

        $userGrupo = (int)substr($identity->username, 5);
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

            // Criar o novo Item
            $itemCode = $tr . '.99';
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
            $votacao->resultado = 'inclusao';
            $votacao->item_modificada = $data['item_modificada'] ?? '';
            $votacao->data = new \Cake\I18n\DateTime();

            if ($this->Votacoes->save($votacao)) {
                $this->Flash->success(__('Novo item {0} inserido e votação registrada.', $itemCode));
                return $this->redirect(['action' => 'index']);
            }

            // Rollback: remover o item criado
            $this->Votacoes->Items->delete($novoItem);
            $this->Flash->error(__('Erro ao salvar a votação do novo item. Tente novamente.'));
        }

        $itemCode = $tr . '.99';
        $this->set(compact('grupo', 'tr', 'itemCode', 'apoioId', 'apoioItem'));
    }

    /**
     * Report method
     *
     * @return \Cake\Http\Response|null|void Renders view
     */
    public function report()
    {
        $this->Authorization->skipAuthorization();

        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        $trInput = $this->request->getQuery('trs');
        $items = [];
        $trList = [];

        if ($trInput !== null && trim($trInput) !== '') {
            $parts = explode(',', $trInput);
            foreach ($parts as $part) {
                $val = trim($part);
                if (is_numeric($val)) {
                    $trList[] = (int)$val;
                }
            }

            if (!empty($trList) && $selectedEventoId) {
                $items = $this->Votacoes->Items->find()
                    ->contain(['Apoios', 'Votacoes' => function ($q) use ($selectedEventoId) {
                        return $q->where(['Votacoes.evento_id' => $selectedEventoId])
                            ->contain(['Users'])
                            ->order(['Votacoes.id' => 'ASC']);
                    }])
                    ->innerJoinWith('Apoios')
                    ->where([
                        'Apoios.evento_id' => $selectedEventoId,
                        'Items.tr IN' => $trList
                    ])
                    ->order(['Items.tr' => 'ASC', 'Items.item' => 'ASC'])
                    ->all();

                // Coletar destaques de minoria
                $destaques = [];
                foreach ($items as $item) {
                    foreach ($item->votacoes as $votacao) {
                        if ($votacao->destaque_minoria) {
                            $destaques[] = [
                                'item' => $item->item,
                                'tr' => $item->tr,
                                'votacao' => $votacao->votacao,
                                'resultado' => $votacao->resultado,
                                'user' => $votacao->user->username ?? '',
                            ];
                        }
                    }
                }
            }
        }

        $destaques = $destaques ?? [];
        $this->set(compact('items', 'trInput', 'trList', 'destaques'));
    }
}
