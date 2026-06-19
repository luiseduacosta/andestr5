<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Apoio> $apoios
 */
?>
<div class="apoios index content">
    <?= $this->Html->link(__('New Apoio'), ['action' => 'add'], ['class' => 'button float-right']) ?>
    <h3><?= __('Apoios') ?></h3>
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('nomedoevento') ?></th>
                    <th><?= $this->Paginator->sort('evento_id') ?></th>
                    <th><?= $this->Paginator->sort('caderno') ?></th>
                    <th><?= $this->Paginator->sort('numero_texto') ?></th>
                    <th><?= $this->Paginator->sort('tema') ?></th>
                    <th><?= $this->Paginator->sort('gt') ?></th>
                    <th><?= $this->Paginator->sort('titulo') ?></th>
                    <th class="actions"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($apoios as $apoio): ?>
                <tr>
                    <td><?= $this->Number->format($apoio->id) ?></td>
                    <td><?= h($apoio->nomedoevento) ?></td>
                    <td><?= $apoio->hasValue('evento') ? $this->Html->link($apoio->evento->nome ?: __('Evento #{0}', $apoio->evento->id), ['controller' => 'Eventos', 'action' => 'view', $apoio->evento->id]) : '' ?></td>
                    <td><?= h($apoio->caderno) ?></td>
                    <td><?= $this->Number->format($apoio->numero_texto) ?></td>
                    <td><?= h($apoio->tema) ?></td>
                    <td><?= h($apoio->gt) ?></td>
                    <td><?= h($apoio->titulo) ?></td>
                    <td class="actions">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $apoio->id]) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $apoio->id]) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $apoio->id], ['confirm' => __('Are you sure you want to delete # {0}?', $apoio->id)]) ?>
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
