<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Votacao $votacao
 * @var string[]|\Cake\Collection\CollectionInterface $users
 * @var string[]|\Cake\Collection\CollectionInterface $eventos
 * @var string[]|\Cake\Collection\CollectionInterface $items
 */
?>
<div class="row g-3">
        <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
            <ul class="navbar-nav ms-auto mt-lg-0">
                <li class="nav-item"><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $votacao->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $votacao->id), 'class' => 'btn btn-outline-danger w-100']
            ) ?></li>
                <li class="nav-item"><?= $this->Html->link(__('List Votacoes'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
            </ul>
        </nav>
        <div class="card shadow-sm">
            <div class="card-body">
            <?= $this->Form->create($votacao, ['class' => 'needs-validation']) ?>
            <fieldset>
                <legend><?= __('Edit Votacao') ?></legend>
                <?php
                    echo $this->Form->control('user_id', ['options' => $users, 'class' => 'form-select', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('evento_id', ['options' => $eventos, 'class' => 'form-select', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('grupo', ['class' => 'form-control', 'label' => ['class' => 'form-label', 'text' => __('Grupo') . '']]);
                    echo $this->Form->control('tr', ['class' => 'form-control', 'label' => ['class' => 'form-label', 'text' => __('TR') . '']]);
                    echo $this->Form->control('item_id', ['options' => $items, 'class' => 'form-select', 'label' => ['class' => 'form-label', 'text' => __('Item Id') . '']]);
                    echo $this->Form->control('item', ['type' => 'text', 'class' => 'form-control', 'label' => ['class' => 'form-label', 'text' => __('Item') . '']]);
                    echo $this->Form->control('resultado', ['class' => 'form-control', 'label' => ['class' => 'form-label', 'text' => __('Resultado') . '']]);
                    echo $this->Form->control('votacao', ['class' => 'form-control', 'label' => ['class' => 'form-label', 'text' => __('Votação') . '']]);
                    echo $this->Form->control('item_modificada', ['class' => 'form-control markdown-editor', 'label' => ['class' => 'form-label', 'text' => __('Modificação/inclusão') . '']]);
                    echo $this->Form->control('data', ['class' => 'form-control', 'label' => ['class' => 'form-label', 'text' => __('Data') . '']]);
                    echo $this->Form->control('observacoes', ['class' => 'form-control markdown-editor', 'label' => ['class' => 'form-label', 'text' => __('Observações') . '']]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->end() ?>
            </div>
        </div>
</div>
