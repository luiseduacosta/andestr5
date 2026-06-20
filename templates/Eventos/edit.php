<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Evento $evento
 */
?>
<div class="row g-3">
        <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
            <ul class="navbar navbar-nav ms-auto mt-lg-0">
                <li class="nav-item"><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $evento->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $evento->id), 'class' => 'btn btn-outline-danger w-100']
            ) ?></li>
                <li class="nav-item"><?= $this->Html->link(__('List Eventos'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
            </ul>
        </nav>
        <div class="card shadow-sm">
            <div class="card-body">
            <?= $this->Form->create($evento, ['class' => 'needs-validation']) ?>
            <fieldset>
                <legend><?= __('Edit Evento') ?></legend>
                <?php
                    echo $this->Form->control('ordem', ['class' => 'form-control', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('nome', ['class' => 'form-control', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('data', ['class' => 'form-control', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('local', ['class' => 'form-control', 'label' => ['class' => 'form-label']]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->end() ?>
            </div>
        </div>
</div>
