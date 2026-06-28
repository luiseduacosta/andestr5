<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Item> $items
 */
?>
<div class="card shadow-sm">
    <div class ="card-header">
        <h3 class="mb-0"><?= __('Items') ?></h3>
        <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
            <?php $identity = $this->request->getAttribute('identity'); ?>
            <?php if (!$identity || ($identity->role !== 'relator')): ?>
            <ul class="navbar navbar-nav ms-auto mt-lg-0">
                <li class="nav-link">
                    <?= $this->Html->link(__('New Item'), ['action' => 'add'], ['class' => 'btn btn-primary']) ?>
                </li>
            </ul>
            <?php endif; ?>
        </nav>
    </div>

    <div class="card-body table-responsive">
        <table class="table table-striped table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th><?= $this->Paginator->sort('id') ?></th>
                    <th><?= $this->Paginator->sort('tr', 'TR') ?></th>
                    <th><?= $this->Paginator->sort('item') ?></th>
                    <th><?= $this->Paginator->sort('texto') ?></th>
                    <th class="d-flex flex-wrap gap-2"><?= __('Actions') ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($items as $item): ?>
                <tr>
                    <td><?= $item->id ?></td>
                    <td><?= $item->hasValue('apoio') ? $this->Html->link($item->apoio->numero_texto, ['controller' => 'Apoios', 'action' => 'view', $item->apoio->id]) : '' ?></td>
                    <td><?= h($item->item) ?></td>
                    <td><?= h($item->texto) ?></td>
                    <td class="d-flex flex-wrap gap-2">
                        <?php
                        $identity = $this->request->getAttribute('identity');
                        $buttonText = '';
                        $buttonLink = null;
                        $buttonClass = '';
                        if ($identity) {
                            if ($identity->role === 'relator') {
                                $userVotacao = null;
                                if (!empty($item->votacoes)) {
                                    foreach ($item->votacoes as $v) {
                                        if ((int)$v->user_id === (int)$identity->id) {
                                            $userVotacao = $v;
                                            break;
                                        }
                                    }
                                }
                                if ($userVotacao) {
                                    $buttonText = __('View');
                                    $buttonLink = ['controller' => 'Votacoes', 'action' => 'view', $userVotacao->id];
                                    $buttonClass = 'btn btn-sm btn-success';
                                } else {
                                    $buttonText = __('Sem votação');
                                    $buttonLink = ['controller' => 'Items', 'action' => 'view', $item->id];
                                    $buttonClass = 'btn btn-sm btn-warning';
                                }
                            } elseif ($identity->role === 'admin' || $identity->role === 'editor') {
                                $hasAnyVotacao = !empty($item->votacoes);
                                if ($hasAnyVotacao) {
                                    $buttonText = __('view');
                                    $buttonLink = ['controller' => 'Votacoes', 'action' => 'index', '?' => ['item_id' => $item->id]];
                                    $buttonClass = 'btn btn-sm btn-success';
                                } else {
                                    $buttonText = __('Sem votação');
                                    $buttonLink = ['controller' => 'Items', 'action' => 'view', $item->id];
                                    $buttonClass = 'btn btn-sm btn-danger';
                                }
                            }
                        }
                        ?>
                        <?php if ($buttonLink): ?>
                            <?= $this->Html->link($buttonText, $buttonLink, ['class' => $buttonClass]) ?>
                        <?php endif; ?>
                        <?= $this->Html->link(__('View'), ['action' => 'view', $item->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                        <?php if (!$identity || ($identity->role !== 'relator')): ?>
                            <?= $this->Html->link(__('Edit'), ['action' => 'edit', $item->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                            <?= $this->Form->postLink(__('Delete'), ['action' => 'delete', $item->id], ['confirm' => __('Are you sure you want to delete # {0}?', $item->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="paginator d-flex justify-content-between align-items-center mt-3">
        <ul class="pagination pagination-sm mb-0">
            <?= $this->Paginator->first('<< ' . __('first')) ?>
            <?= $this->Paginator->prev('< ' . __('previous')) ?>
            <?= $this->Paginator->numbers() ?>
            <?= $this->Paginator->next(__('next') . ' >') ?>
            <?= $this->Paginator->last(__('last') . ' >>') ?>
        </ul>
        <p class="text-body-secondary small mb-0"><?= $this->Paginator->counter(__('Page {{page}} of {{pages}}, showing {{current}} record(s) out of {{count}} total')) ?></p>
    </div>
</div>