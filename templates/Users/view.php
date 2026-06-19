<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit User'), ['action' => 'edit', $user->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete User'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete # {0}?', $user->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Users'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New User'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="users view content">
            <h3><?= h($user->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('Username') ?></th>
                    <td><?= h($user->username) ?></td>
                </tr>
                <tr>
                    <th><?= __('Role') ?></th>
                    <td><?= h($user->role) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($user->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($user->created) ?></td>
                </tr>
                <tr>
                    <th><?= __('Modified') ?></th>
                    <td><?= h($user->modified) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __('Related Votacoes') ?></h4>
                <?php if (!empty($user->votacoes)) : ?>
                <div class="table-responsive">
                    <table>
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
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($user->votacoes as $votacao) : ?>
                        <tr>
                            <td><?= h($votacao->id) ?></td>
                            <td><?= h($votacao->user_id) ?></td>
                            <td><?= h($votacao->evento_id) ?></td>
                            <td><?= h($votacao->grupo) ?></td>
                            <td><?= h($votacao->tr) ?></td>
                            <td><?= h($votacao->tr_suprimida) ?></td>
                            <td><?= h($votacao->tr_aprovada) ?></td>
                            <td><?= h($votacao->item_id) ?></td>
                            <td><?= h($votacao->item) ?></td>
                            <td><?= h($votacao->resultado) ?></td>
                            <td><?= h($votacao->votacao) ?></td>
                            <td><?= h($votacao->item_modificada) ?></td>
                            <td><?= h($votacao->data) ?></td>
                            <td><?= h($votacao->observacoes) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Votacoes', 'action' => 'view', $votacao->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Votacoes', 'action' => 'edit', $votacao->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'Votacoes', 'action' => 'delete', $votacao->id], ['confirm' => __('Are you sure you want to delete # {0}?', $votacao->id)]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>