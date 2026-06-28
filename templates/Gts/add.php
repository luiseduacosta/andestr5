<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Gt $gt
 */
?>
<div class="row g-3">
    <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
        <ul class="navbar-nav ms-auto mt-lg-0">
            <li class="nav-item"><?= $this->Html->link(__('List Gts'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
        </ul>
    </nav>

    <div class="card shadow-sm">
        <div class="card-body">
            <h4><?= __('Add Gt') ?></h4>
            <?= $this->Form->create($gt, ['class' => 'needs-validation']) ?>
            <fieldset>
                <legend><?= __('Gt Information') ?></legend>
                <?php
                    echo $this->Form->control('sigla', [
                        'class' => 'form-control',
                        'label' => ['class' => 'form-label'],
                    ]);
                    echo $this->Form->control('nome', [
                        'class' => 'form-control',
                        'label' => ['class' => 'form-label'],
                    ]);
                    echo $this->Form->control('outras', [
                        'class' => 'form-control',
                        'label' => ['class' => 'form-label'],
                    ]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Save'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
