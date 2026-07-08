<?php
/**
 * Fase 4 — Inserir novo item durante a votação
 *
 * @var \App\View\AppView $this
 * @var int $grupo
 * @var int $tr
 * @var string $itemCode
 * @var int $apoioId
 * @var \App\Model\Entity\Item $apoioItem
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
            <h4><?= __('Fase 4 — Inserir Novo Item (TR {0}, Grupo {1})', $tr, $grupo) ?></h4>
            <p class="text-muted"><?= __('O grupo pode propor e votar um novo item para esta TR. Será criado com o código {0}.', $itemCode) ?></p>

            <div class="card bg-light mb-3">
                <div class="card-body">
                    <h5><?= __('Apoio de referência:') ?></h5>
                    <p><strong><?= h($apoioItem->apoio->titulo ?? 'Apoio #' . $apoioId) ?></strong></p>
                    <p class="text-muted small"><?= __('O novo item será vinculado a este apoio (apoio_id: {0}).', $apoioId) ?></p>
                </div>
            </div>

            <?= $this->Form->create(null, ['url' => ['action' => 'inserirItem', $grupo, $tr], 'class' => 'needs-validation']) ?>
            <fieldset>
                <legend><?= __('Novo Item') ?></legend>
                <?php
                    echo $this->Form->control('texto', [
                        'type' => 'textarea',
                        'class' => 'form-control markdown-editor',
                        'label' => ['class' => 'form-label', 'text' => __('Texto do novo item')],
                        'rows' => 5,
                        'required' => true,
                    ]);
                ?>
            </fieldset>
            <fieldset class="mt-3">
                <legend><?= __('Votação do novo item') ?></legend>
                <?php
                    echo $this->Form->control('resultado', [
                        'type' => 'hidden',
                        'value' => 'inclusão',
                    ]);
                ?>
                <p class="text-muted"><em><?= __('O resultado será registrado automaticamente como "inclusão".') ?></em></p>
                <?php
                    echo $this->Form->control('votacao', [
                        'class' => 'form-control',
                        'label' => ['class' => 'form-label'],
                        'placeholder' => 'ex: 12/3/0',
                        'help' => __('Formato: favoráveis/contrários/abstenções'),
                    ]);
                    echo $this->Form->control('destaque_minoria', [
                        'type' => 'checkbox',
                        'class' => 'form-check-input',
                        'label' => ['class' => 'form-check-label', 'text' => __('Destaque de minoria (≥ 1/3 dos votantes)')],
                    ]);
                    echo $this->Form->control('observacoes', [
                        'class' => 'form-control markdown-editor',
                        'label' => ['class' => 'form-label'],
                    ]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Inserir Item e Registrar Votação'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
