<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Evento $evento
 */
?>
<div class="row g-3">
        <?php $identity = $this->request->getAttribute('identity'); ?>
        <?php if (!$identity || ($identity->role !== 'relator')): ?>
        <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
            <ul class="navbar navbar-nav ms-auto mt-lg-0">
                <li class="nav-item"><?= $this->Html->link(__('Edit Evento'), ['action' => 'edit', $evento->id], ['class' => 'btn btn-primary w-100']) ?></li>
                <li class="nav-item"><?= $this->Form->postLink(__('Delete Evento'), ['action' => 'delete', $evento->id], ['confirm' => __('Are you sure you want to delete # {0}?', $evento->id), 'class' => 'btn btn-outline-danger w-100']) ?></li>
                <li class="nav-item"><?= $this->Html->link(__('List Eventos'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
                <li class="nav-item"><?= $this->Html->link(__('New Evento'), ['action' => 'add'], ['class' => 'btn btn-outline-primary w-100']) ?></li>
            </ul>
        </nav>
        <?php endif; ?>
        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="card-title"><?= h($evento->nome) ?></h2>
            </div>
            <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-secondary"><?= __('Nome') ?></dt>
                <dd class="col-sm-8"><?= h($evento->nome) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Data') ?></dt>
                <dd class="col-sm-8"><?= h($evento->data) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Local') ?></dt>
                <dd class="col-sm-8"><?= h($evento->local) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Id') ?></dt>
                <dd class="col-sm-8"><?= $this->Number->format($evento->id) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Ordem') ?></dt>
                <dd class="col-sm-8"><?= $this->Number->format($evento->ordem) ?></dd>
            </dl>
            <div class="card mt-4">
                <h4><?= __('Apoios') ?></h4>
                <?php if (!empty($evento->apoios)) : ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= __('Id') ?></th>
                                <th><?= __('Evento') ?></th>
                                <th><?= __('Evento Id') ?></th>
                                <th><?= __('Caderno') ?></th>
                                <th><?= __('Numero Texto') ?></th>
                                <th><?= __('Tema') ?></th>
                                <th><?= __('Gt') ?></th>
                                <th><?= __('Titulo') ?></th>
                                <th><?= __('Autor') ?></th>
                                <th><?= __('Texto') ?></th>
                                <th class="d-flex flex-wrap gap-2"><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($evento->apoios as $apoio) : ?>
                        <tr>
                            <td><?= h($apoio->id) ?></td>
                            <td><?= h($apoio->nomedoevento) ?></td>
                            <td><?= h($apoio->evento_id) ?></td>
                            <td><?= h($apoio->caderno) ?></td>
                            <td><?= h($apoio->numero_texto) ?></td>
                            <td><?= h($apoio->tema) ?></td>
                            <td><?= h($apoio->gt) ?></td>
                            <td><?= h($apoio->titulo) ?></td>
                            <td><?= $apoio->autor ?></td>
                            <td><?= $apoio->texto ?></td>
                            <td class="d-flex flex-wrap gap-2">
                                <?= $this->Html->link(__('View'), ['controller' => 'Apoios', 'action' => 'view', $apoio->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                <?php if (!$identity || ($identity->role !== 'relator')): ?>
                                    <?= $this->Html->link(__('Edit'), ['controller' => 'Apoios', 'action' => 'edit', $apoio->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Apoios', 'action' => 'delete', $apoio->id], ['confirm' => __('Are you sure you want to delete # {0}?', $apoio->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="card mt-4">
                <div class="card-header">
                    <h4><?= __('Votacoes') ?></h4>
                </div>
                <?php if (!empty($evento->votacoes)) : ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= __('Id') ?></th>
                                <th><?= __('User Id') ?></th>
                                <th><?= __('Evento Id') ?></th>
                                <th><?= __('Grupo') ?></th>
                                <th><?= __('Tr') ?></th>
                                <th><?= __('Tr Suprimida') ?></th>
                                <th><?= __('Tr Aprovada') ?></th>
                                <th><?= __('Item Id') ?></th>
                                <th><?= __('Item') ?></th>
                                <th><?= __('Resultado') ?></th>
                                <th><?= __('Votacao') ?></th>
                                <th><?= __('Item Modificada') ?></th>
                                <th><?= __('Data') ?></th>
                                <th><?= __('Observacoes') ?></th>
                                <th class="d-flex flex-wrap gap-2"><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($evento->votacoes as $votacao) : ?>
                        <tr>
                            <td><?= h($votacao->id) ?></td>
                            <td><?= h($votacao->user_id) ?></td>
                            <td><?= h($votacao->evento_id) ?></td>
                            <td><?= h($votacao->grupo) ?></td>
                            <td><?= h($votacao->tr) ?></td>
                            <td><?= h($votacao->item_id) ?></td>
                            <td><?= h($votacao->item) ?></td>
                            <td><?= h($votacao->resultado) ?></td>
                            <td><?= h($votacao->votacao) ?></td>
                            <td><?= h($votacao->item_modificada) ?></td>
                            <td><?= h($votacao->data) ?></td>
                            <td><?= h($votacao->observacoes) ?></td>
                            <td class="d-flex flex-wrap gap-2">
                                <?= $this->Html->link(__('View'), ['controller' => 'Votacoes', 'action' => 'view', $votacao->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                <?php if (!$identity || ($identity->role !== 'relator')): ?>
                                    <?= $this->Html->link(__('Edit'), ['controller' => 'Votacoes', 'action' => 'edit', $votacao->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Votacoes', 'action' => 'delete', $votacao->id], ['confirm' => __('Are you sure you want to delete # {0}?', $votacao->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
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
</div>