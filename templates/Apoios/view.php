<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Apoio $apoio
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Apoio'), ['action' => 'edit', $apoio->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Apoio'), ['action' => 'delete', $apoio->id], ['confirm' => __('Are you sure you want to delete # {0}?', $apoio->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Apoios'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Apoio'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="apoios view content">
            <h3><?= h($apoio->caderno) ?></h3>
            <table>
                <tr>
                    <th><?= __('Nomedoevento') ?></th>
                    <td><?= h($apoio->nomedoevento) ?></td>
                </tr>
                <tr>
                    <th><?= __('Evento') ?></th>
                    <td><?= $apoio->hasValue('evento') ? $this->Html->link($apoio->evento->nome ?: __('Evento #{0}', $apoio->evento->id), ['controller' => 'Eventos', 'action' => 'view', $apoio->evento->id]) : '' ?></td>
                </tr>
                <tr>
                    <th><?= __('Caderno') ?></th>
                    <td><?= h($apoio->caderno) ?></td>
                </tr>
                <tr>
                    <th><?= __('Tema') ?></th>
                    <td><?= h($apoio->tema) ?></td>
                </tr>
                <tr>
                    <th><?= __('Gt') ?></th>
                    <td><?= h($apoio->gt) ?></td>
                </tr>
                <tr>
                    <th><?= __('Titulo') ?></th>
                    <td><?= h($apoio->titulo) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($apoio->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Numero Texto') ?></th>
                    <td><?= $this->Number->format($apoio->numero_texto) ?></td>
                </tr>
            </table>
            <div class="text">
                <strong><?= __('Autor') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($apoio->autor)); ?>
                </blockquote>
            </div>
            <div class="text">
                <strong><?= __('Texto') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($apoio->texto)); ?>
                </blockquote>
            </div>
            <div class="related">
                <h4><?= __('Related Items') ?></h4>
                <?php if (!empty($apoio->items)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Apoio Id') ?></th>
                            <th><?= __('Tr') ?></th>
                            <th><?= __('Item') ?></th>
                            <th><?= __('Texto') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($apoio->items as $item) : ?>
                        <tr>
                            <td><?= h($item->id) ?></td>
                            <td><?= h($item->apoio_id) ?></td>
                            <td><?= h($item->tr) ?></td>
                            <td><?= h($item->item) ?></td>
                            <td><?= h($item->texto) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Items', 'action' => 'view', $item->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Items', 'action' => 'edit', $item->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'Items', 'action' => 'delete', $item->id], ['confirm' => __('Are you sure you want to delete # {0}?', $item->id)]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
