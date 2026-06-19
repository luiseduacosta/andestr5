<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Apoio $apoio
 * @var string[]|\Cake\Collection\CollectionInterface $eventos
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $apoio->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $apoio->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Apoios'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="apoios form content">
            <?= $this->Form->create($apoio) ?>
            <fieldset>
                <legend><?= __('Edit Apoio') ?></legend>
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
