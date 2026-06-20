<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Item $item
 */
?>
<div class="card shadow-sm">
    <div class="card-header">
        <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
            <ul class="navbar navbar-nav ms-auto mt-lg-0">
                <li class="nav-item"><?= $this->Html->link(__('Edit Item'), ['action' => 'edit', $item->id], ['class' => 'btn btn-primary w-100']) ?></li>
                <li class="nav-item"><?= $this->Form->postLink(__('Delete Item'), ['action' => 'delete', $item->id], ['confirm' => __('Are you sure you want to delete # {0}?', $item->id), 'class' => 'btn btn-outline-danger w-100']) ?></li>
                <li class="nav-item"><?= $this->Html->link(__('List Items'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
                <li class="nav-item"><?= $this->Html->link(__('New Item'), ['action' => 'add'], ['class' => 'btn btn-outline-primary w-100']) ?></li>
            </ul>
        </nav>
    </div>
    <div class="card-body">
            <h3><?= h($item->item) ?></h3>
            <dl class="row mb-0">
                <dt class="col-sm-4 text-secondary"><?= __('Apoio') ?></dt>
                <dd class="col-sm-8"><?= $item->hasValue('apoio') ? $this->Html->link($item->apoio->caderno, ['controller' => 'Apoios', 'action' => 'view', $item->apoio->id]) : '' ?></dd>
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
                                <th><?= __('Tr Suprimida', 'Suprimida') ?></th>
                                <th><?= __('Tr Aprovada', 'Aprovada') ?></th>
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
                        <tr>
                            <td><?= h($votacao->id) ?></td>
                            <td><?= h($votacao->user_id) ?></td>
                            <td><?= h($votacao->evento_id) ?></td>
                            <td><?= h($votacao->grupo) ?></td>
                            <td><?= h($votacao->tr) ?></td>
                            <td><?= h($votacao->tr_suprimida) ?></td>
                            <td><?= h($votacao->tr_aprovada) ?></td>
                            <td><?= h($votacao->item_id) ?></td>
                            <td><?= h($votacao->item) ?></td>
                            <td><?= h($votacao->resultado) ?></td>
                            <td><?= h($votacao->votacao) ?></td>
                            <td><?= $votacao->item_modificada ?></td>
                            <td><?= h($votacao->data) ?></td>
                            <td><?= h($votacao->observacoes) ?></td>
                            <td class="d-flex flex-wrap gap-2">
                                <?= $this->Html->link(__('View'), ['controller' => 'Votacoes', 'action' => 'view', $votacao->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Votacoes', 'action' => 'edit', $votacao->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'Votacoes', 'action' => 'delete', $votacao->id], ['confirm' => __('Are you sure you want to delete # {0}?', $votacao->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
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