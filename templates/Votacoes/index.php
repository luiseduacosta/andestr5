<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Votacao> $votacoes
 */
?>
<div class="card shadow-sm">

    <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded mb-3">
        <h3 class="mb-0"><?= __('Votações') ?></h3>
        <ul class="navbar-nav ms-auto mt-lg-0">
            <li class="nav-item">
                <?= $this->Html->link(__('New Votacao'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
            </li>
        </ul>
    </nav>

    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('user_id') ?></th>
                    <th><?= $this->Paginator->sort('evento_id') ?></th>
                    <th><?= $this->Paginator->sort('grupo') ?></th>
                    <th><?= $this->Paginator->sort('tr') ?></th>
                    <th><?= $this->Paginator->sort('tr_suprimida') ?></th>
                    <th><?= $this->Paginator->sort('tr_aprovada') ?></th>
                    <th><?= $this->Paginator->sort('item_id') ?></th>
                    <th><?= $this->Paginator->sort('item') ?></th>
                    <th><?= $this->Paginator->sort('resultado') ?></th>
                    <th><?= $this->Paginator->sort('votacao') ?></th>
                    <th><?= $this->Paginator->sort('data') ?></th>
                    <th class="text-center"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($votacoes as $votacao): ?>
                <tr>
                    <td><?= $this->Number->format($votacao->id) ?></td>
                    <td><?= $votacao->hasValue('user') ? $this->Html->link($votacao->user->username, ['controller' => 'Users', 'action' => 'view', $votacao->user->id]) : '' ?></td>
                    <td><?= $votacao->hasValue('evento') ? $this->Html->link($votacao->evento->nome ?: __('Evento #{0}', $votacao->evento->id), ['controller' => 'Eventos', 'action' => 'view', $votacao->evento->id]) : '' ?></td>
                    <td><?= $this->Number->format($votacao->grupo) ?></td>
                    <td><?= $this->Number->format($votacao->tr) ?></td>
                    <td><?= $this->Number->format($votacao->tr_suprimida) ?></td>
                    <td><?= $this->Number->format($votacao->tr_aprovada) ?></td>
                    <td><?= $votacao->hasValue('votacao_item') ? $this->Html->link($votacao->votacao_item->item, ['controller' => 'Items', 'action' => 'view', $votacao->votacao_item->id]) : '' ?></td>
                    <td><?= h($votacao->item) ?></td>
                    <td><?= h($votacao->resultado) ?></td>
                    <td><?= h($votacao->votacao) ?></td>
                    <td><?= h($votacao->data) ?></td>
                    <td class="text-center">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $votacao->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $votacao->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $votacao->id], ['confirm' => __('Are you sure you want to delete # {0}?', $votacao->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="paginator d-flex justify-content-between align-items-center mt-3">
        <ul class="pagination pagination-sm mb-0">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p class="text-body-secondary small mb-0"><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
