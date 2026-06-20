<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 */
?>
<div class="row g-3">
        <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
            <ul class="navbar navbar-nav ms-auto mt-lg-0">
                <li class="nav-item"><?= $this->Html->link(__('List Users'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
            </ul>
        </nav>
        <div class="card shadow-sm">
            <div class="card-body">
            <?= $this->Form->create($user, ['class' => 'needs-validation']) ?>
            <fieldset>
                <legend><?= __('Usuário') ?></legend>
                <?php
                    echo $this->Form->control('username', ['class' => 'form-control', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('password', ['class' => 'form-control', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('role', ['class' => 'form-control', 'label' => ['class' => 'form-label']]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->end() ?>
            </div>
        </div>
</div>
