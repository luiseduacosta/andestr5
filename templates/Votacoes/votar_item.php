<?php
/**
 * Fase 2 — Votação individual de item em discussão
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Item $item
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
            <h4><?= __('Fase 2 — Votação do Item {0} (TR {1})', $item->item, $item->tr) ?></h4>

            <div class="card bg-light mb-3">
                <div class="card-body">
                    <h5><?= __('Texto original:') ?></h5>
                    <p><?= h($item->texto) ?></p>
                </div>
            </div>

            <?= $this->Form->create(null, ['url' => ['action' => 'votarItem', $item->id], 'class' => 'needs-validation']) ?>
            <fieldset>
                <legend><?= __('Registro da votação do item') ?></legend>
                <?php
                    echo $this->Form->control('resultado', [
                        'options' => [
                            'Aprovado' => __('Aprovado'),
                            'Rejeitado' => __('Rejeitado'),
                            'Modificado' => __('Aprovado com modificação'),
                        ],
                        'class' => 'form-select',
                        'label' => ['class' => 'form-label'],
                        'empty' => false,
                    ]);
                    echo $this->Form->control('votacao', [
                        'class' => 'form-control',
                        'label' => ['class' => 'form-label'],
                        'placeholder' => 'ex: 9/6/0',
                        'help' => __('Formato: favoráveis/contrários/abstenções'),
                    ]);
                    echo $this->Form->control('item_modificada', [
                        'class' => 'form-control markdown-editor',
                        'label' => ['class' => 'form-label'],
                        'help' => __('Preencha apenas se houve modificação aprovada.'),
                    ]);
                    echo $this->Form->control('destaque_minoria', [
                        'type' => 'checkbox',
                        'class' => 'form-check-input',
                        'label' => ['class' => 'form-check-label', 'text' => __('Destaque de minoria (≥ 1/3 dos votantes)')],
                        'help' => __('Marque se o lado perdedor obteve 1/3 ou mais dos votos totais.'),
                    ]);
                    echo $this->Form->control('tr_aprovada', [
                        'type' => 'checkbox',
                        'class' => 'form-check-input',
                        'label' => ['class' => 'form-check-label', 'text' => __('TR aprovada sem modificações')],
                        'help' => __('Marque se a TR não foi rejeitada e nenhum item teve modificação aprovada.'),
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
