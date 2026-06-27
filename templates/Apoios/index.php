<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Apoio> $apoios
 */
?>
<div class="container py-4">
    <?php $identity = $this->request->getAttribute('identity'); ?>
    <?php if (!$identity || ($identity->role !== 'relator')): ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded mb-3">
        <ul class="navbar navbar-nav ms-auto mt-lg-0">
            <li class="nav-item">
                <?= $this->Html->link(__('New Apoio'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
            </li>
        </ul>
    </nav>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('nomedoevento') ?></th>
                    <th><?= $this->Paginator->sort('evento_id') ?></th>
                    <th><?= $this->Paginator->sort('caderno') ?></th>
                    <th><?= $this->Paginator->sort('numero_texto', 'Texto') ?></th>
                    <th><?= $this->Paginator->sort('tema') ?></th>
                    <th><?= $this->Paginator->sort('gt', 'GT') ?></th>
                    <th><?= $this->Paginator->sort('titulo') ?></th>
                    <th class="d-flex flex-wrap gap-2"><?= __('Actions') ?></th>
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
                    <td class="d-flex flex-wrap gap-2">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $apoio->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?php if (!$identity || ($identity->role !== 'relator')): ?>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $apoio->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $apoio->id], ['confirm' => __('Are you sure you want to delete # {0}?', $apoio->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                        <?php endif; ?>
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
