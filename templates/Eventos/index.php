<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Evento> $eventos
 */
?>
<div class="card">
    <div class="card-header">
        <h2 class="card-title"><?= __('Eventos') ?></h2>
   </div>
    <?php $identity = $this->request->getAttribute('identity'); ?>
    <?php if (!$identity || ($identity->role !== 'relator')): ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <ul class="navbar navbar-nav ms-auto mt-lg-0">
            <li class="nav-item"><?= $this->Html->link(__('New Evento'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?></li>
        </ul>
    </nav>
    <?php endif; ?>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('ordem') ?></th>
                    <th><?= $this->Paginator->sort('nome') ?></th>
                    <th><?= $this->Paginator->sort('data') ?></th>
                    <th><?= $this->Paginator->sort('local') ?></th>
                    <th class="d-flex flex-wrap gap-2"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($eventos as $evento): ?>
                <tr>
                    <td><?= $this->Number->format($evento->id) ?></td>
                    <td><?= $this->Number->format($evento->ordem) ?></td>
                    <td>
                        <?= h($evento->nome) ?>
                        <?php if ($evento->ativo): ?>
                            <span class="badge bg-success ms-1"><?= __('Ativo') ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= h($evento->data) ?></td>
                    <td><?= h($evento->local) ?></td>
                    <td class="d-flex flex-wrap gap-2">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $evento->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?php $identity = $this->request->getAttribute('identity'); ?>
                        <?php if (!$identity || ($identity->role !== 'relator')): ?>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $evento->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $evento->id], ['confirm' => __('Are you sure you want to delete # {0}?', $evento->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                        <?php endif; ?>
                        <?php if ($identity && ($identity->role === 'admin' || $identity->role === 'editor') && !$evento->ativo): ?>
                            <?= $this->Form->postLink(__('Ativar'), ['action' => 'ativar', $evento->id], ['confirm' => __('Ativar este evento para votação?'), 'class' => 'btn btn-sm btn-outline-success']) ?>
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