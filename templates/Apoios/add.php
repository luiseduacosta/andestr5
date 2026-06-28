<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Apoio $apoio
 * @var \Cake\Collection\CollectionInterface|string[] $eventos
 */
?>
<?php $caderno = ['Principal' => 'Principal', 'Anexo' => 'Anexo'] ?>
<div class="row g-3">
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <ul class="navbar navbar-nav ms-auto p-2">
            <li class="nav-item"><?= $this->Html->link(__('List Apoios'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
        </ul>
    </nav>

    <div class="card shadow-sm">
        <div class="card-header">
            <h2 class="card-title"><?= __('Adicionar Texto de Apoio') ?></h2>
        </div>
        <div class="card-body">
            <?= $this->Form->create($apoio, ['class' => 'needs-validation']) ?>
            <fieldset>
                <?php
                    echo $this->Form->control('evento_id', ['options' => $eventos, 'class' => 'form-select', 'label' => ['text' => __('Evento'), 'class' => 'form-label']]);
                    echo $this->Form->control('caderno', ['class' => 'form-control', 'label' => ['text' => __('Caderno'), 'class' => 'form-label'], 'options' => $caderno]);
                    echo $this->Form->control('numero_texto', ['class' => 'form-control', 'label' => ['text' => __('Número do Texto'), 'class' => 'form-label']]);
                    echo $this->Form->control('tema', ['class' => 'form-control', 'label' => ['text' => __('Tema'), 'class' => 'form-label']]);
                    echo $this->Form->control('gt_id', ['class' => 'form-control', 'label' => ['text' => __('Grupo de Trabalho'), 'class' => 'form-label'], 'options' => $gts]);
                    echo $this->Form->control('titulo', ['class' => 'form-control', 'label' => ['text' => __('Título'), 'class' => 'form-label']]);
                    echo $this->Form->control('autor', ['class' => 'form-control markdown-editor', 'label' => ['text' => __('Autor'), 'class' => 'form-label']]);
                    echo $this->Form->control('texto', ['class' => 'form-control markdown-editor', 'label' => ['text' => __('Texto'), 'class' => 'form-label']]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
