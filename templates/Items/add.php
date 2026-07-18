<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Item $item
 * @var \Cake\Collection\CollectionInterface|string[] $apoios
 */
?>
<div class="row g-3">
        <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
            <ul class="navbar navbar-nav ms-auto mt-lg-0">
                <li class="nav-item"><?= $this->Html->link(__('List Items'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
            </ul>
        </nav>
        <div class="card shadow-sm">
            <div class="card-body">
            <?= $this->Form->create($item, ['class' => 'needs-validation']) ?>
            <fieldset>
                <legend><?= __('Add Item') ?></legend>
                <?php
                    echo $this->Form->control('apoio_id', ['options' => $apoios, 'class' => 'form-select', 'label' => ['text' => __('Apoio'), 'class' => 'form-label']]);
                    echo $this->Form->control('tr', ['value' => $trFilter, 'class' => 'form-control', 'label' => ['text' => __('TR'), 'class' => 'form-label']]);
                    echo $this->Form->control('item', ['value' => $nextItemValue, 'class' => 'form-control', 'label' => ['text' => __('Item'), 'class' => 'form-label']]);
                    echo $this->Form->control('texto', ['class' => 'form-control markdown-editor', 'label' => ['text' => __('Texto'), 'class' => 'form-label']]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->end() ?>
            </div>
        </div>
</div>
