<?php
/**
 * Fase 3 — Aprovação em bloco dos itens não discutidos
 *
 * @var \App\View\AppView $this
 * @var \Cake\Collection\Collection\CollectionInterface<\App\Model\Entity\Item> $itensRestantes
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
            <h4><?= __('Fase 3 — Aprovação em bloco dos itens não discutidos (TR {0}, Grupo {1})', $tr, $grupo) ?></h4>
            <p class="text-muted"><?= __('Os itens abaixo não foram destacados para discussão e serão votados afirmativamente em bloco.') ?></p>

            <h5><?= __('Itens a aprovar ({0}):', $itensRestantes->count()) ?></h5>
            <table class="table table-striped table-sm mb-3">
                <thead>
                    <tr>
                        <th><?= __('Item') ?></th>
                        <th><?= __('Texto') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($itensRestantes as $item): ?>
                    <tr>
                        <td><?= h($item->item) ?></td>
                        <td><?= h($item->texto) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?= $this->Form->create(null, ['url' => ['action' => 'votarRestantes', $grupo, $tr], 'class' => 'needs-validation']) ?>
            <fieldset>
                <legend><?= __('Votação afirmativa') ?></legend>
                <p><?= __('Será registrado <strong>Aprovado</strong> para cada um dos {0} itens acima.', $itensRestantes->count()) ?></p>
                <?php
                    echo $this->Form->control('votacao', [
                        'class' => 'form-control',
                        'label' => ['class' => 'form-label'],
                        'placeholder' => 'ex: 15/0/0',
                        'help' => __('Formato: favoráveis/contrários/abstenções'),
                    ]);
                    echo $this->Form->control('observacoes', [
                        'class' => 'form-control markdown-editor',
                        'label' => ['class' => 'form-label'],
                    ]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Registrar Aprovação em Bloco'), ['class' => 'btn btn-success']) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
