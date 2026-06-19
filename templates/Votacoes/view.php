<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Votacao $votacao
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Votacao'), ['action' => 'edit', $votacao->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Votacao'), ['action' => 'delete', $votacao->id], ['confirm' => __('Are you sure you want to delete # {0}?', $votacao->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Votacoes'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Votacao'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="votacoes view content">
            <h3><?= h($votacao->item) ?></h3>
            <table>
                <tr>
                    <th><?= __('User') ?></th>
                    <td><?= $votacao->hasValue('user') ? $this->Html->link($votacao->user->id, ['controller' => 'Users', 'action' => 'view', $votacao->user->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Evento') ?></th>
                    <td><?= $votacao->hasValue('evento') ? $this->Html->link($votacao->evento->nome ?: __('Evento #{0}', $votacao->evento->id), ['controller' => 'Eventos', 'action' => 'view', $votacao->evento->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Item') ?></th>
                    <td><?= $votacao->hasValue('item') ? $this->Html->link($votacao->item->item, ['controller' => 'Items', 'action' => 'view', $votacao->item->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Item') ?></th>
                    <td><?= h($votacao->item) ?></td>
                </tr>
                <tr>
                    <th><?= __('Resultado') ?></th>
                    <td><?= h($votacao->resultado) ?></td>
                </tr>
                <tr>
                    <th><?= __('Votacao') ?></th>
                    <td><?= h($votacao->votacao) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($votacao->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Grupo') ?></th>
                    <td><?= $this->Number->format($votacao->grupo) ?></td>
                </tr>
                <tr>
                    <th><?= __('Tr') ?></th>
                    <td><?= $this->Number->format($votacao->tr) ?></td>
                </tr>
                <tr>
                    <th><?= __('Tr Suprimida') ?></th>
                    <td><?= $this->Number->format($votacao->tr_suprimida) ?></td>
                </tr>
                <tr>
                    <th><?= __('Tr Aprovada') ?></th>
                    <td><?= $this->Number->format($votacao->tr_aprovada) ?></td>
                </tr>
                <tr>
                    <th><?= __('Data') ?></th>
                    <td><?= h($votacao->data) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Item Modificada') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($votacao->item_modificada)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Observacoes') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($votacao->observacoes)); ?>
                </blockquote>
            </div>
        </div>
    </div>
</div>
