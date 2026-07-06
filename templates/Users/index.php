<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\User> $users
 */
$identity = $this->request->getAttribute('identity');
$isAdmin = $identity && $identity->role === 'admin';
?>
<div class="card shadow-sm">
    <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded mb-3">
        <h3 class="mb-0"><?= __('Usuários') ?></h3>
        <ul class="navbar-nav ms-auto mt-lg-0">
            <li class="nav-item"><?= $this->Html->link(__('New User'), ['action' => 'add'], ['class' => 'btn btn-primary w-100']) ?></li>
        </ul>
    </nav>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('username') ?></th>
                    <th><?= $this->Paginator->sort('role') ?></th>
                    <th><?= $this->Paginator->sort('created') ?></th>
                    <th><?= $this->Paginator->sort('modified') ?></th>
                    <th class="d-flex flex-wrap gap-2"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $this->Number->format($user->id) ?></td>
                    <td><?= h($user->username) ?></td>
                    <td><?= h($user->role) ?></td>
                    <td><?= h($user->created) ?></td>
                    <td><?= h($user->modified) ?></td>
                    <td class="d-flex flex-wrap gap-2">
                        <?= $this->Html->link(__('View'), ['action' => 'view', $user->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?= $this->Html->link(__('Edit'), ['action' => 'edit', $user->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                        <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $user->id], ['confirm' => __('Are you sure you want to delete # {0}?', $user->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                        <?php if ($isAdmin && (int)$identity->id !== (int)$user->id): ?>
                            <?= $this->Form->postLink(
                                __('Impersonate'),
                                ['action' => 'impersonate', $user->id],
                                ['confirm' => __('Are you sure you want to impersonate {0}?', h($user->username)), 'class' => 'btn btn-sm btn-outline-warning']
                            ) ?>
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