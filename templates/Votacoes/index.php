<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Votacao> $votacoes
 */
?>
<div class="votacoes index content">
    <?= $this->Html->link(__('New Votacao'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Votacoes') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
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
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($votacoes as $votacao): ?>
                <tr>
                    <td><?= $this->Number->format($votacao->id) ?></td>
                    <td><?= $votacao->hasValue('user') ? $this->Html->link($votacao->user->id, ['controller' => 'Users', 'action' => 'view', $votacao->user->id]) : '' ?></td>
                    <td><?= $votacao->hasValue('evento') ? $this->Html->link($votacao->evento->nome ?: __('Evento #{0}', $votacao->evento->id), ['controller' => 'Eventos', 'action' => 'view', $votacao->evento->id]) : '' ?></td>
                    <td><?= $this->Number->format($votacao->grupo) ?></td>
                    <td><?= $this->Number->format($votacao->tr) ?></td>
                    <td><?= $this->Number->format($votacao->tr_suprimida) ?></td>
                    <td><?= $this->Number->format($votacao->tr_aprovada) ?></td>
                    <td><?= $votacao->hasValue('item') ? $this->Html->link($votacao->item->item, ['controller' => 'Items', 'action' => 'view', $votacao->item->id]) : '' ?></td>
                    <td><?= h($votacao->item) ?></td>
                    <td><?= h($votacao->resultado) ?></td>
                    <td><?= h($votacao->votacao) ?></td>
                    <td><?= h($votacao->data) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $votacao->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $votacao->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $votacao->id], ['confirm' => __('Are you sure you want to delete # {0}?', $votacao->id)]) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="paginator">
        <ul class="pagination">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>
