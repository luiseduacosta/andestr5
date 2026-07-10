<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
$identity = $this->request->getAttribute('identity');
$isEditorOrAdmin = $identity && ($identity->role === 'admin' || $identity->role === 'editor');
$canDelete = $isEditorOrAdmin && ($identity && (int)$identity->id !== (int)$user->id);
?>
<div class="row g-3">
        <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
            <ul class="navbar-nav flex-column w-100">
                <?php if ($canDelete): ?>
                    <li class="nav-item"><?= $this->Form->postLink(
                        __('Delete'),
                        ['action' => 'delete', $user->id],
                        ['confirm' => __('Are you sure you want to delete # {0}?', $user->id), 'class' => 'btn btn-outline-danger w-100']
                    ) ?></li>
                <?php endif; ?>
                <?php if ($isEditorOrAdmin): ?>
                    <li class="nav-item"><?= $this->Html->link(__('List Users'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="card shadow-sm">
            <div class="card-body">
            <?= $this->Form->create($user, ['class' => 'needs-validation']) ?>
            <fieldset>
                <legend><?= __('Edit User') ?></legend>
                <?php
                    echo $this->Form->control('username', ['class' => 'form-control', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('password', ['class' => 'form-control', 'label' => ['class' => 'form-label'], 'value' => '', 'required' => false]);
                    if ($isEditorOrAdmin) {
                        echo $this->Form->control('role', [
                            'type' => 'select',
                            'options' => [
                                'admin' => 'Admin',
                                'editor' => 'Editor',
                                'relator' => 'Relator',
                                'user' => 'User'
                            ],
                            'class' => 'form-control',
                            'label' => ['class' => 'form-label']
                        ]);
                    }
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->end() ?>
            </div>
        </div>
</div>
