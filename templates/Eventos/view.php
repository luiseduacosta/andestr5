<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Evento $evento
 */
pr($evento);
die();
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Evento'), ['action' => 'edit', $evento->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Evento'), ['action' => 'delete', $evento->id], ['confirm' => __('Are you sure you want to delete # {0}?', $evento->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Eventos'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Evento'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column column-80">
        <div class="eventos view content">
            <h3><?= h($evento->evento) ?></h3>
            <table>
                <tr>
                    <th><?= __('Nome') ?></th>
                    <td><?= h($evento->nome) ?></td>
                </tr>
                <tr>
                    <th><?= __('Data') ?></th>
                    <td><?= h($evento->data) ?></td>
                </tr>
                <tr>
                    <th><?= __('Local') ?></th>
                    <td><?= h($evento->local) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($evento->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Ordem') ?></th>
                    <td><?= $this->Number->format($evento->ordem) ?></td>
                </tr>
            </table>
            <div class="related">
                <h4><?= __('Related Apoios') ?></h4>
                <?php if (!empty($evento->apoios)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('Nomedoevento') ?></th>
                            <th><?= __('Evento Id') ?></th>
                            <th><?= __('Caderno') ?></th>
                            <th><?= __('Numero Texto') ?></th>
                            <th><?= __('Tema') ?></th>
                            <th><?= __('Gt') ?></th>
                            <th><?= __('Titulo') ?></th>
                            <th><?= __('Autor') ?></th>
                            <th><?= __('Texto') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($evento->apoios as $apoio) : ?>
                        <tr>
                            <td><?= h($apoio->id) ?></td>
                            <td><?= h($apoio->nomedoevento) ?></td>
                            <td><?= h($apoio->evento_id) ?></td>
                            <td><?= h($apoio->caderno) ?></td>
                            <td><?= h($apoio->numero_texto) ?></td>
                            <td><?= h($apoio->tema) ?></td>
                            <td><?= h($apoio->gt) ?></td>
                            <td><?= h($apoio->titulo) ?></td>
                            <td><?= h($apoio->autor) ?></td>
                            <td><?= h($apoio->texto) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Apoios', 'action' => 'view', $apoio->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Apoios', 'action' => 'edit', $apoio->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'Apoios', 'action' => 'delete', $apoio->id], ['confirm' => __('Are you sure you want to delete # {0}?', $apoio->id)]) ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="related">
                <h4><?= __('Related Votacoes') ?></h4>
                <?php if (!empty($evento->votacoes)) : ?>
                <div class="table-responsive">
                    <table>
                        <tr>
                            <th><?= __('Id') ?></th>
                            <th><?= __('User Id') ?></th>
                            <th><?= __('Evento Id') ?></th>
                            <th><?= __('Grupo') ?></th>
                            <th><?= __('Tr') ?></th>
                            <th><?= __('Tr Suprimida') ?></th>
                            <th><?= __('Tr Aprovada') ?></th>
                            <th><?= __('Item Id') ?></th>
                            <th><?= __('Item') ?></th>
                            <th><?= __('Resultado') ?></th>
                            <th><?= __('Votacao') ?></th>
                            <th><?= __('Item Modificada') ?></th>
                            <th><?= __('Data') ?></th>
                            <th><?= __('Observacoes') ?></th>
                            <th class="actions"><?= __('Actions') ?></th>
                        </tr>
                        <?php foreach ($evento->votacoes as $votacao) : ?>
                        <tr>
                            <td><?= h($votacao->id) ?></td>
                            <td><?= h($votacao->user_id) ?></td>
                            <td><?= h($votacao->evento_id) ?></td>
                            <td><?= h($votacao->grupo) ?></td>
                            <td><?= h($votacao->tr) ?></td>
                            <td><?= h($votacao->tr_suprimida) ?></td>
                            <td><?= h($votacao->tr_aprovada) ?></td>
                            <td><?= h($votacao->item_id) ?></td>
                            <td><?= h($votacao->item) ?></td>
                            <td><?= h($votacao->resultado) ?></td>
                            <td><?= h($votacao->votacao) ?></td>
                            <td><?= h($votacao->item_modificada) ?></td>
                            <td><?= h($votacao->data) ?></td>
                            <td><?= h($votacao->observacoes) ?></td>
                            <td class="actions">
                                <?= $this->Html->link(__('View'), ['controller' => 'Votacoes', 'action' => 'view', $votacao->id]) ?>
                                <?= $this->Html->link(__('Edit'), ['controller' => 'Votacoes', 'action' => 'edit', $votacao->id]) ?>
                                <?= $this->Form->postLink(__('Delete'), ['controller' => 'Votacoes', 'action' => 'delete', $votacao->id], ['confirm' => __('Are you sure you want to delete # {0}?', $votacao->id)]) ?>
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