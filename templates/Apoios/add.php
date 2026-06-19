<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Apoio $apoio
 * @var \Cake\Collection\CollectionInterface|string[] $eventos
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Apoios'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="apoios form content">
            <?= $this->Form->create($apoio) ?>
            <fieldset>
                <legend><?= __('Add Apoio') ?></legend>
                <?php
                    echo $this->Form->control('nomedoevento');
                    echo $this->Form->control('evento_id', ['options' => $eventos]);
                    echo $this->Form->control('caderno');
                    echo $this->Form->control('numero_texto');
                    echo $this->Form->control('tema');
                    echo $this->Form->control('gt');
                    echo $this->Form->control('titulo');
                    echo $this->Form->control('autor');
                    echo $this->Form->control('texto');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
