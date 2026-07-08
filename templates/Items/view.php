<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Item $item
 */
?>
<?php $identity = $this->request->getAttribute('identity'); ?>
<div class="card shadow-sm">
    <div class="card-header">
        <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
            <ul class="navbar navbar-nav ms-auto mt-lg-0">
                <li class="nav-item"><?= $this->Html->link(__('List Items'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
                <?php if (!$identity || ($identity->role !== 'relator')): ?>
                    <li class="nav-item"><?= $this->Html->link(__('Edit Item'), ['action' => 'edit', $item->id], ['class' => 'btn btn-primary w-100']) ?></li>
                    <li class="nav-item"><?= $this->Form->postLink(__('Delete Item'), ['action' => 'delete', $item->id], ['confirm' => __('Are you sure you want to delete # {0}?', $item->id), 'class' => 'btn btn-outline-danger w-100']) ?></li>
                    <li class="nav-item"><?= $this->Html->link(__('New Item'), ['action' => 'add'], ['class' => 'btn btn-outline-primary w-100']) ?></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
    <div class="card-body">
            <h3><?= h($item->item) ?></h3>
            <dl class="row mb-0">
                <dt class="col-sm-4 text-secondary"><?= __('Apoio') ?></dt>
                <dd class="col-sm-8"><?= $item->hasValue('apoio') ? $this->Html->link($item->apoio->numero_texto, ['controller' => 'Apoios', 'action' => 'view', $item->apoio->id]) : '' ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Item') ?></dt>
                <dd class="col-sm-8"><?= h($item->item) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Id') ?></dt>
                <dd class="col-sm-8"><?= $this->Number->format($item->id) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('TR') ?></dt>
                <dd class="col-sm-8"><?= $this->Number->format($item->tr) ?></dd>
            </dl>
            <div class="text">
                <strong><?= __('Texto') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph($item->texto); ?>
                </blockquote>
            </div>

            <?php
                $grupo = null;
                $isPrivilegedUser = $identity && in_array($identity->role, ['admin', 'editor'], true);
                $identityGroup = null;
                if ($identity && $identity->role === 'relator') {
                    $grupo = (int)substr((string)$identity->username, 5);
                    $identityGroup = $grupo;
                }
                $tr = $item->tr;
            ?>
            <?php if ($grupo && $tr): ?>
            <div class="card border-primary-subtle bg-primary-subtle bg-opacity-10 mt-4">
                <div class="card-header">
                    <h5 class="mb-0"><?= __('⚡ Fluxo de Votação') ?></h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <div class="card border-danger h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-title"><?= __('Fase 1 — Votação da TR') ?></h6>
                                    <p class="card-text small text-muted"><?= __('Rejeitar toda a TR {0} (Grupo {1})', $tr, $grupo) ?></p>
                                    <?= $this->Html->link(__('Ir para Fase 1'), ['controller' => 'Votacoes', 'action' => 'votarTr', $grupo, $tr], ['class' => 'btn btn-outline-danger btn-sm']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-warning h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-title"><?= __('Fase 2 — Votar este Item') ?></h6>
                                    <p class="card-text small text-muted"><?= __('Item {0} em discussão. Registrar voto, modificação e destaque.', $item->item) ?></p>
                                    <?= $this->Html->link(__('Ir para Fase 2'), ['controller' => 'Votacoes', 'action' => 'votarItem', $item->id], ['class' => 'btn btn-outline-warning btn-sm']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-success h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-title"><?= __('Fase 3 — Aprovar Restantes') ?></h6>
                                    <p class="card-text small text-muted"><?= __('Voto afirmativo em bloco nos itens não discutidos da TR {0}.', $tr) ?></p>
                                    <?= $this->Html->link(__('Ir para Fase 3'), ['controller' => 'Votacoes', 'action' => 'votarRestantes', $grupo, $tr], ['class' => 'btn btn-outline-success btn-sm']) ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-info h-100">
                                <div class="card-body text-center">
                                    <h6 class="card-title"><?= __('Fase 4 — Inserir Novo Item') ?></h6>
                                    <p class="card-text small text-muted"><?= __('Propor e votar um novo item para a TR {0} (código {0}.99).', $tr) ?></p>
                                    <?= $this->Html->link(__('Ir para Fase 4'), ['controller' => 'Votacoes', 'action' => 'inserirItem', $grupo, $tr], ['class' => 'btn btn-outline-info btn-sm']) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="card mt-4">
                <h4><?= __('Votações') ?></h4>
                <?php if (!empty($item->votacoes)) : ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= __('Id') ?></th>
                                <th><?= __('User Id', 'Usuário') ?></th>
                                <th><?= __('Evento Id', 'Evento') ?></th>
                                <th><?= __('Grupo') ?></th>
                                <th><?= __('Tr', 'TR') ?></th>
                                <th><?= __('Item Id', 'Id item') ?></th>
                                <th><?= __('Item') ?></th>
                                <th><?= __('Resultado') ?></th>
                                <th><?= __('Votacao') ?></th>
                                <th><?= __('Item Modificada', 'Modificada') ?></th>
                                <th><?= __('Data') ?></th>
                                <th><?= __('Observacoes') ?></th>
                                <th class="d-flex flex-wrap gap-2"><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($item->votacoes as $votacao) : ?>
                        <?php
                            $canEditVotacao = $isPrivilegedUser || ($identityGroup !== null && $identityGroup === (int)$votacao->grupo);
                            $canDeleteVotacao = $isPrivilegedUser || ($identityGroup !== null && $identityGroup === (int)$votacao->grupo);
                        ?>
                        <tr>
                            <td><?= h($votacao->id) ?></td>
                            <td><?= h($votacao->user_id) ?></td>
                            <td><?= h($votacao->evento_id) ?></td>
                            <td><?= h($votacao->grupo) ?></td>
                            <td><?= h($votacao->tr) ?></td>
                            <td><?= h($votacao->item_id) ?></td>
                            <td><?= h($votacao->item) ?></td>
                            <td><?= h($votacao->resultado) ?></td>
                            <td><?= h($votacao->votacao) ?></td>
                            <td><?= $votacao->item_modificada ?></td>
                            <td><?= h($votacao->data) ?></td>
                            <td><?= h($votacao->observacoes) ?></td>
                            <td class="d-flex flex-wrap gap-2">
                                <?= $this->Html->link(__('View'), ['controller' => 'Votacoes', 'action' => 'view', $votacao->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                <?php if ($canEditVotacao) : ?>
                                    <?= $this->Html->link(__('Edit'), ['controller' => 'Votacoes', 'action' => 'edit', $votacao->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                <?php endif; ?>
                                <?php if ($canDeleteVotacao) : ?>
                                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Votacoes', 'action' => 'delete', $votacao->id], ['confirm' => __('Are you sure you want to delete # {0}?', $votacao->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
    </div>
</div>
