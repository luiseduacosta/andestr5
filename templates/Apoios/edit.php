<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Apoio $apoio
 * @var string[]|\Cake\Collection\CollectionInterface $eventos
 */
?>
<div class="row g-3">
        <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
            <ul class="navbar navbar-nav ms-auto mt-lg-0">
                <li class="nav-item"><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $apoio->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $apoio->id), 'class' => 'btn btn-outline-danger w-100']
            ) ?></li>
                <li class="nav-item"><?= $this->Html->link(__('List Apoios'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
            </ul>
        </nav>
        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="card-title"><?= __('Edit Apoio') ?></h2>
            </div>
            <div class="card-body">
            <?= $this->Form->create($apoio, ['class' => 'needs-validation']) ?>
            <fieldset>
                <?php
                    echo $this->Form->control('nomedoevento', ['class' => 'form-control', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('evento_id', ['options' => $eventos, 'class' => 'form-select', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('caderno', ['class' => 'form-control', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('numero_texto', ['class' => 'form-control', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('tema', ['class' => 'form-control', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('gt', ['class' => 'form-control', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('titulo', ['class' => 'form-control', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('autor', ['class' => 'form-control markdown-editor', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('texto', ['class' => 'form-control markdown-editor', 'label' => ['class' => 'form-label']]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->end() ?>
            </div>
        </div>
</div>
