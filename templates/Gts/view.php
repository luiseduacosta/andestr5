<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Gt $gt
 */
?>
<div class="row g-3">
    <?php $identity = $this->request->getAttribute('identity'); ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
        <ul class="navbar navbar-nav ms-auto mt-lg-0">
            <li class="nav-item"><?= $this->Html->link(__('List Gts'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
            <?php if ($identity && in_array($identity->role, ['admin', 'editor'])): ?>
            <li class="nav-item"><?= $this->Html->link(__('Edit Gt'), ['action' => 'edit', $gt->id], ['class' => 'btn btn-primary w-100']) ?></li>
            <?php endif; ?>
            <?php if ($identity && $identity->role === 'admin'): ?>
            <li class="nav-item"><?= $this->Form->postLink(__('Delete Gt'), ['action' => 'delete', $gt->id], ['confirm' => __('Are you sure you want to delete # {0}?', $gt->id), 'class' => 'btn btn-outline-danger w-100']) ?></li>
            <?php endif; ?>
            <?php if ($identity && in_array($identity->role, ['admin', 'editor'])): ?>
            <li class="nav-item"><?= $this->Html->link(__('New Gt'), ['action' => 'add'], ['class' => 'btn btn-outline-primary w-100']) ?></li>
            <?php endif; ?>
        </ul>
    </nav>
    
    <div class="card shadow-sm">
        <div class="card-header">
            <h2 class="card-title"><?= h($gt->sigla) ?></h2>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-secondary"><?= __('Id') ?></dt>
                <dd class="col-sm-8"><?= $this->Number->format($gt->id) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Sigla') ?></dt>
                <dd class="col-sm-8"><?= h($gt->sigla) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Nome') ?></dt>
                <dd class="col-sm-8"><?= h($gt->nome) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Outras') ?></dt>
                <dd class="col-sm-8"><?= h($gt->outras) ?></dd>
            </dl>
        </div>
    </div>

    <div class="card mt-4">
        <div class="card-header">
            <h4><?= __('Related Apoios') ?></h4>
        </div>
        <?php if (!empty($gt->apoios_list)) : ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th><?= __('Id') ?></th>
                        <th><?= __('Numero Texto') ?></th>
                        <th><?= __('Titulo') ?></th>
                        <th><?= __('Autor') ?></th>
                        <th class="d-flex flex-wrap gap-2"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($gt->apoios_list as $apoio) : ?>
                    <tr>
                        <td><?= h($apoio->id) ?></td>
                        <td><?= h($apoio->numero_texto) ?></td>
                        <td><?= h($apoio->titulo) ?></td>
                        <td><?= h($apoio->autor) ?></td>
                        <td class="d-flex flex-wrap gap-2">
                            <?= $this->Html->link(__('View'), ['controller' => 'Apoios', 'action' => 'view', $apoio->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
