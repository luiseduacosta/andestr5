<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Votacao $votacao
 * @var \Cake\Collection\CollectionInterface|string[] $users
 * @var \Cake\Collection\CollectionInterface|string[] $eventos
 * @var \Cake\Collection\CollectionInterface|string[] $items
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('List Votacoes'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="votacoes form content">
            <?= $this->Form->create($votacao) ?>
            <fieldset>
                <legend><?= __('Add Votacao') ?></legend>
                <?php
                    echo $this->Form->control('user_id', ['options' => $users]);
                    echo $this->Form->control('evento_id', ['options' => $eventos]);
                    echo $this->Form->control('grupo');
                    echo $this->Form->control('tr');
                    echo $this->Form->control('tr_suprimida');
                    echo $this->Form->control('tr_aprovada');
                    echo $this->Form->control('item_id', ['options' => $items]);
                    echo $this->Form->control('item');
                    echo $this->Form->control('resultado');
                    echo $this->Form->control('votacao');
                    echo $this->Form->control('item_modificada');
                    echo $this->Form->control('data');
                    echo $this->Form->control('observacoes');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
