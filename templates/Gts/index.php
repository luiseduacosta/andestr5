<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Gt> $gts
 */
?>
<div class="row g-3">
    <?php $identity = $this->request->getAttribute('identity'); ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
        <ul class="navbar-nav ms-auto mt-lg-0">
            <?php if ($identity && in_array($identity->role, ['admin', 'editor'])): ?>
            <li class="nav-item"><?= $this->Html->link(__('New Gt'), ['action' => 'add'], ['class' => 'btn btn-outline-primary w-100']) ?></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="card shadow-sm">
        <div class="card-body">
            <h2><?= __('Gts') ?></h2>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th><?= $this->Paginator->sort('id') ?></th>
                            <th><?= $this->Paginator->sort('sigla') ?></th>
                            <th><?= $this->Paginator->sort('nome') ?></th>
                            <th><?= $this->Paginator->sort('outras') ?></th>
                            <th><?= __('Actions') ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($gts as $gt): ?>
                        <tr>
                            <td><?= $this->Number->format($gt->id) ?></td>
                            <td><?= h($gt->sigla) ?></td>
                            <td><?= h($gt->nome) ?></td>
                            <td><?= h($gt->outras) ?></td>
                            <td class="d-flex flex-wrap gap-2">
                                <?= $this->Html->link(__('View'), ['action' => 'view', $gt->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                <?php if ($identity && in_array($identity->role, ['admin', 'editor'])): ?>
                                    <?= $this->Html->link(__('Edit'), ['action' => 'edit', $gt->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                <?php endif; ?>
                                <?php if ($identity && $identity->role === 'admin'): ?>
                                    <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $gt->id], ['confirm' => __('Are you sure you want to delete # {0}?', $gt->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
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
    </div>
</div>
