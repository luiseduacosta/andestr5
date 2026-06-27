<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="card shadow-sm">

        <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
            <ul class="navbar navbar-nav ms-auto mt-lg-0">
                <li class="nav-item"><?= $this->Html->link(__('Edit User'), ['action' => 'edit', $user->id], ['class' => 'btn btn-primary w-100']) ?></li>
                <li class="nav-item"><?= $this->Form->postLink(__('Delete User'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete # {0}?', $user->id), 'class' => 'btn btn-outline-danger w-100']) ?></li>
                <li class="nav-item"><?= $this->Html->link(__('List Users'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
                <li class="nav-item"><?= $this->Html->link(__('New User'), ['action' => 'add'], ['class' => 'btn btn-outline-primary w-100']) ?></li>
            </ul>
        </nav>

        <div class="card shadow-sm">
            <div class="card-body">
                <h3><?= h($user->id) ?></h3>
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-secondary"><?= __('Username') ?></dt>
                    <dd class="col-sm-8"><?= h($user->username) ?></dd>
                    <dt class="col-sm-4 text-secondary"><?= __('Role') ?></dt>
                    <dd class="col-sm-8"><?= h($user->role) ?></dd>
                    <dt class="col-sm-4 text-secondary"><?= __('Id') ?></dt>
                    <dd class="col-sm-8"><?= $this->Number->format($user->id) ?></dd>
                    <dt class="col-sm-4 text-secondary"><?= __('Created') ?></dt>
                    <dd class="col-sm-8"><?= h($user->created) ?></dd>
                    <dt class="col-sm-4 text-secondary"><?= __('Modified') ?></dt>
                    <dd class="col-sm-8"><?= h($user->modified) ?></dd>
                </dl>
                <div class="card mt-4">
                    <h4><?= __('Votações') ?></h4>
                    <?php if (!empty($user->votacoes)) : ?>
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
                            <?php foreach ($user->votacoes as $votacao) : ?>
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
                                    <?= $this->Html->link(__('Edit'), ['controller' => 'Votacoes', 'action' => 'edit', $votacao->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Votacoes', 'action' => 'delete', $votacao->id], ['confirm' => __('Are you sure you want to delete # {0}?', $votacao->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
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