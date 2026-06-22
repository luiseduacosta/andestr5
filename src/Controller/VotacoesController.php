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
            ->contain(['Users', 'Eventos', 'Items']);

        $selectedEventoId = $this->request->getSession()->read('selected_evento_id');
        if ($selectedEventoId) {
            $query->where(['Votacoes.evento_id' => $selectedEventoId]);
        }

        $itemId = $this->request->getQuery('item_id');
        if ($itemId) {
            $query->where(['Votacoes.item_id' => $itemId]);
        }

        $identity = $this->Authentication->getIdentity();
        if ($identity && $identity->role === 'relator') {
            $query->where(['Votacoes.user_id' => $identity->id]);
        }

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
        $users = $this->Votacoes->Users->find('list', limit: 200)->all();
        $eventos = $this->Votacoes->Eventos->find('list', limit: 200)->all();

        $identity = $this->Authentication->getIdentity();
        $itemsQuery = $this->Votacoes->Items->find('list');
        if ($selectedEventoId) {
            $itemsQuery->innerJoinWith('Apoios')
                ->where(['Apoios.evento_id' => $selectedEventoId]);
        }
        if ($identity && $identity->role === 'relator') {
            $itemsQuery->where(['OR' => [
                ['Items.item NOT LIKE' => '%99'],
                ['Items.user_id' => $identity->id]
            ]]);
        }
        $items = $itemsQuery->all();

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
        $users = $this->Votacoes->Users->find('list', limit: 200)->all();
        $eventos = $this->Votacoes->Eventos->find('list', limit: 200)->all();

        $identity = $this->Authentication->getIdentity();
        $itemsQuery = $this->Votacoes->Items->find('list');
        if ($selectedEventoId) {
            $itemsQuery->innerJoinWith('Apoios')
                ->where(['Apoios.evento_id' => $selectedEventoId]);
        }
        if ($identity && $identity->role === 'relator') {
            $itemsQuery->where(['OR' => [
                ['Items.item NOT LIKE' => '%99'],
                ['Items.user_id' => $identity->id]
            ]]);
        }
        $items = $itemsQuery->all();

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

        // Buscar itens desta TR no evento ativo
        $itensTr = $this->Votacoes->Items->find()
            ->innerJoinWith('Apoios')
            ->where([
                'Apoios.evento_id' => $selectedEventoId,
                'Items.tr' => $tr,
            ])
            ->order(['Items.item' => 'ASC'])
            ->all();

        if ($itensTr->isEmpty()) {
            $this->Flash->error(__('Nenhum item encontrado para esta TR.'));
            return $this->redirect(['action' => 'index']);
        }

        if ($this->request->is('post')) {
            $data = $this->request->getData();
            $resultado = $data['resultado'] ?? '';

            if ($resultado === 'Rejeitado') {
                $entities = [];
                foreach ($itensTr as $item) {
                    $votacao = $this->Votacoes->newEmptyEntity();
                    $votacao->user_id = $identity->id;
                    $votacao->evento_id = $selectedEventoId;
                    $votacao->grupo = $grupo;
                    $votacao->tr = $tr;
                    $votacao->tr_suprimida = 1;
                    $votacao->tr_aprovada = 0;
                    $votacao->item_id = $item->id;
                    $votacao->item = $item->item;
                    $votacao->resultado = 'Rejeitado';
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

            $votacao = $this->Votacoes->newEmptyEntity();
            $votacao = $this->Votacoes->patchEntity($votacao, $data);
            $votacao->user_id = $identity->id;
            $votacao->evento_id = $selectedEventoId;
            $votacao->grupo = $item->apoio->gt_id ?? 0;
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

        // Usar o finder personalizado
        $itensRestantes = $this->Votacoes->findItensSemVoto(
            $this->Votacoes->Items->find(),
            ['grupo' => $grupo, 'tr' => $tr, 'evento_id' => $selectedEventoId]
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
                $votacao->tr_suprimida = 0;
                $votacao->tr_aprovada = 0;
                $votacao->item_id = $item->id;
                $votacao->item = $item->item;
                $votacao->resultado = 'Aprovado';
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
