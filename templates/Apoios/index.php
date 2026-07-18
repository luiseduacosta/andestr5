<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Apoio> $apoios
 */
?>
<div class="container py-4">
    <?php $identity = $this->request->getAttribute('identity'); ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-light p-3 rounded mb-3">
        <div class="container-fluid d-flex justify-content-between align-items-center flex-wrap gap-2">
            <form class="d-flex flex-grow-1 me-lg-3" method="get" action="" style="max-width: 500px;">
                <input class="form-control me-2" type="search" name="search" placeholder="<?= __('Search by author or text...') ?>" aria-label="Search" value="<?= h($this->request->getQuery('search')) ?>">
                <button class="btn btn-outline-primary" type="submit"><?= __('Search') ?></button>
                <?php if ($this->request->getQuery('search')): ?>
                    <?= $this->Html->link(__('Clear'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary ms-2']) ?>
                <?php endif; ?>
            </form>
            <?php if (!$identity || ($identity->role !== 'relator')): ?>
                <div>
                    <?= $this->Html->link(__('New Apoio'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
                </div>
            <?php endif; ?>
        </div>
    </nav>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('nomedoevento', 'Evento') ?></th>
                    <th><?= $this->Paginator->sort('caderno', 'Caderno') ?></th>
                    <th><?= $this->Paginator->sort('numero_texto', 'Número') ?></th>
                    <th><?= $this->Paginator->sort('gt', 'GT') ?></th>                    
                    <th><?= $this->Paginator->sort('titulo', 'Título') ?></th>
                    <th class="d-flex flex-wrap gap-2"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($apoios as $apoio): ?>
                <tr>
                    <td><?= $this->Number->format($apoio->id) ?></td>
                    <td><?= $apoio->hasValue('evento') ? $this->Html->link($apoio->evento->nome ?: __('Evento #{0}', $apoio->evento->id), ['controller' => 'Eventos', 'action' => 'view', $apoio->evento->id]) : '' ?></td>
                    <td><?= h($apoio->caderno) ?></td>
                    <td><?= $this->Number->format($apoio->numero_texto) ?></td>
                    <td><?= !empty($apoio->gt_entity) ? h($apoio->gt_entity->sigla) : __('N/A') ?></td>
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
