<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="row justify-content-center">
    <div class="col-12 col-md-8 col-lg-5">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-lg-5">
                <div class="text-center mb-4">
                    <h1 class="h3 mb-2"><?= __('Login') ?></h1>
                    <p class="text-body-secondary mb-0"><?= __('Sign in to access the Andestr dashboard.') ?></p>
                </div>

                <?= $this->Form->create(null, ['class' => 'login-form']) ?>
                <div class="mb-3">
                    <?= $this->Form->label('username', __('Username'), ['class' => 'form-label']) ?>
                    <?= $this->Form->text('username', ['class' => 'form-control', 'required' => true]) ?>
                </div>
                <div class="mb-4">
                    <?= $this->Form->label('password', __('Password'), ['class' => 'form-label']) ?>
                    <?= $this->Form->password('password', ['class' => 'form-control', 'required' => true]) ?>
                </div>
                <div class="d-grid gap-2">
                    <?= $this->Form->button(__('Login'), ['class' => 'btn btn-primary btn-lg']) ?>
                    <?= $this->Html->link(__('Back to Eventos'), ['controller' => 'Eventos', 'action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
                </div>
                <?= $this->Form->end() ?>

                <p class="small text-body-secondary mt-4 mb-0">
                    <?= __('This screen is ready for styling and navigation. Hook up authentication next when credentials and roles are defined.') ?>
                </p>
            </div>
        </div>
    </div>
</div>
