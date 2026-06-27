<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Apoio $apoio
 */
?>
<div class="row g-3">
    <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
        <ul class="navbar navbar-nav ms-auto mt-lg-0">
            <?php $identity = $this->request->getAttribute('identity'); ?>
            <?php if (!$identity || ($identity->role !== 'relator')): ?>
                <li class="nav-item"><?= $this->Html->link(__('New Apoio'), ['action' => 'add'], ['class' => 'btn btn-outline-primary w-100']) ?></li>
                <li class="nav-item"><?= $this->Html->link(__('Edit Apoio'), ['action' => 'edit', $apoio->id], ['class' => 'btn btn-primary w-100']) ?></li>
                <li class="nav-item"><?= $this->Form->postLink(__('Delete Apoio'), ['action' => 'delete', $apoio->id], ['confirm' => __('Are you sure you want to delete # {0}?', $apoio->id), 'class' => 'btn btn-outline-danger w-100']) ?></li>
            <?php endif; ?>
            <li class="nav-item"><?= $this->Html->link(__('List Apoios'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
        </ul>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header">
            <h2 class="card-title"><?= h($apoio->caderno) ?></h2>
        </div>
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-secondary"><?= __('Id') ?></dt>
                <dd class="col-sm-8"><?= $this->Number->format($apoio->id) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Evento') ?></dt>
                <dd class="col-sm-8"><?= $apoio->hasValue('evento') ? $this->Html->link($apoio->evento->nome ?: __('Evento #{0}', $apoio->evento->id), ['controller' => 'Eventos', 'action' => 'view', $apoio->evento->id]) : '' ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Caderno') ?></dt>
                <dd class="col-sm-8"><?= h($apoio->caderno) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Tema') ?></dt>
                <dd class="col-sm-8"><?= h($apoio->tema) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Grupo trabalho') ?></dt>
                <dd class="col-sm-8"><?= h($apoio->gt) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Titulo') ?></dt>
                <dd class="col-sm-8"><?= h($apoio->titulo) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Texto') ?></dt>
                <dd class="col-sm-8"><?= $this->Number->format($apoio->numero_texto) ?></dd>
            </dl>
            <div class="text">
                <strong><?= __('Autor') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph($apoio->autor); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Texto') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph($apoio->texto); ?>
                </blockquote>
            </div>
            <div class="card mt-4">
                <div class="card-header">
                    <h3 class="card-title"><?= __('Items') ?></h3>
                <div class="card-body">
                <?php if (!empty($apoio->items)) : ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= __('Id') ?></th>
                                <th><?= __('Apoio') ?></th>
                                <th><?= __('TR') ?></th>
                                <th><?= __('Item') ?></th>
                                <th><?= __('Texto') ?></th>
                                <th class="d-flex flex-wrap gap-2"><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($apoio->items as $item) : ?>
                        <tr>
                            <td><?= h($item->id) ?></td>
                            <td><?= h($item->apoio_id) ?></td>
                            <td><?= h($item->tr) ?></td>
                            <td><?= h($item->item) ?></td>
                            <td><?= $item->texto ?></td>
                            <td class="d-flex flex-wrap gap-2">
                                <?= $this->Html->link(__('View'), ['controller' => 'Items', 'action' => 'view', $item->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                <?php if (!$identity || ($identity->role !== 'relator')): ?>
                                    <?= $this->Html->link(__('Edit'), ['controller' => 'Items', 'action' => 'edit', $item->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Items', 'action' => 'delete', $item->id], ['confirm' => __('Are you sure you want to delete # {0}?', $item->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
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
</div>
