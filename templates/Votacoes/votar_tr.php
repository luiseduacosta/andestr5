<?php
/**
 * Fase 1 — Votação da TR inteira (rejeição opcional)
 *
 * @var \App\View\AppView $this
 * @var \Cake\Collection\CollectionInterface<\App\Model\Entity\Item> $itensTr
 * @var int $grupo
 * @var int $tr
 */
?>
<div class="row g-3">
    <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
        <ul class="navbar-nav ms-auto mt-lg-0">
            <li class="nav-item"><?= $this->Html->link(__('List Votacoes'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
        </ul>
    </nav>

    <div class="card shadow-sm">
        <div class="card-body">
            <h4><?= __('Fase 1 — Votação da TR {0} (Grupo {1})', $tr, $grupo) ?></h4>
            <p class="text-muted"><?= __('Esta fase só é necessária se houve proposta de rejeição da TR inteira.') ?></p>

            <h5><?= __('Itens desta TR:') ?></h5>
            <table class="table table-striped table-sm mb-3">
                <thead>
                    <tr>
                        <th><?= __('Item') ?></th>
                        <th><?= __('Texto original') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itensTr as $item): ?>
                    <tr>
                        <td><?= h($item->item) ?></td>
                        <td><?= h($item->texto) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?= $this->Form->create(null, ['url' => ['action' => 'votarTr', $grupo, $tr], 'class' => 'needs-validation']) ?>
            <fieldset>
                <legend><?= __('Resultado da votação') ?></legend>
                <?php
                    echo $this->Form->control('resultado', [
                        'options' => [
                            'aprovada' => __('TR aprovada (prossegue sem rejeição)'), 
                            'suprimida' => __('TR suprimida (rejeitada)')
                        ],
                        'class' => 'form-select',
                        'label' => ['class' => 'form-label'],
                        'empty' => false,
                    ]);
                    echo $this->Form->control('votacao', [
                        'class' => 'form-control',
                        'label' => ['class' => 'form-label'],
                        'placeholder' => 'ex: 12/3/0',
                        'help' => __('Formato: favoráveis/contrários/abstenções'),
                    ]);
                    echo $this->Form->control('observacoes', [
                        'class' => 'form-control markdown-editor',
                        'label' => ['class' => 'form-label'],
                    ]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Registrar Votação'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
