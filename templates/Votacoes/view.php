<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Votacao $votacao
 */
?>
<?php
    $identity = $this->request->getAttribute('identity');
    $isPrivilegedUser = $identity && in_array($identity->role, ['admin', 'editor'], true);
    $identityGroup = $identity && $identity->role === 'relator' ? (int)substr((string)$identity->username, 5) : null;
    $canEditVotacao = $isPrivilegedUser || ($identityGroup !== null && $identityGroup === (int)$votacao->grupo);
    $canDeleteVotacao = $isPrivilegedUser || ($identityGroup !== null && $identityGroup === (int)$votacao->grupo);
?>
<div class="row g-3">

        <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
            <ul class="navbar-nav ms-auto mt-lg-0">
                <?php if ($canEditVotacao) : ?>
                    <li class="nav-item"><?= $this->Html->link(__('Edit Votacao'), ['action' => 'edit', $votacao->id], ['class' => 'btn btn-primary w-100']) ?></li>
                <?php endif; ?>
                <?php if ($canDeleteVotacao) : ?>
                    <li class="nav-item"><?= $this->Form->postLink(__('Delete Votacao'), ['action' => 'delete', $votacao->id], ['confirm' => __('Are you sure you want to delete # {0}?', $votacao->id), 'class' => 'btn btn-outline-danger w-100']) ?></li>
                <?php endif; ?>
                <li class="nav-item"><?= $this->Html->link(__('List Votacoes'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
                <li class="nav-item"><?= $this->Html->link(__('New Votacao'), ['action' => 'add'], ['class' => 'btn btn-outline-primary w-100']) ?></li>
            </ul>
        </nav>

        <div class="card shadow-sm">
            <div class="card-body">
            <h3><?= h($votacao->item) ?></h3>
            <dl class="row mb-0">
                <dt class="col-sm-4 text-secondary"><?= __('Id') ?></dt>
                <dd class="col-sm-8"><?= $this->Number->format($votacao->id) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('TR') ?></dt>
                <dd class="col-sm-8"><?= $this->Html->link($this->Number->format($votacao->tr), ['controller' => 'Apoios', 'action' => 'viewtr', '?' => ['evento_id' => $votacao->evento->id, 'tr' => $votacao->tr]]) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Usuário') ?></dt>
                <?php if ($identity && $identity->role === 'relator') : ?>
                    <dd class="col-sm-8"><?= $votacao->hasValue('user') ? $votacao->user->username : '' ?></dd>
                <?php else: ?>    
                    <dd class="col-sm-8"><?= $votacao->hasValue('user') ? $this->Html->link($votacao->user->username, ['controller' => 'Users', 'action' => 'view', $votacao->user->id]) : '' ?></dd>
                <?php endif; ?>
                <dt class="col-sm-4 text-secondary"><?= __('Evento') ?></dt>
                <dd class="col-sm-8"><?= $votacao->hasValue('evento') ? $this->Html->link($votacao->evento->nome ?: __('Evento #{0}', $votacao->evento->id), ['controller' => 'Eventos', 'action' => 'view', $votacao->evento->id]) : '' ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Item') ?></dt>
                <dd class="col-sm-8"><?= $votacao->hasValue('votacao_item') ? $this->Html->link($votacao->votacao_item->item, ['controller' => 'Items', 'action' => 'view', $votacao->votacao_item->id]) : '' ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Resultado') ?></dt>
                <dd class="col-sm-8"><?= h($votacao->resultado) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Votação') ?></dt>
                <dd class="col-sm-8"><?= h($votacao->votacao) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Grupo') ?></dt>
                <dd class="col-sm-8"><?= $this->Number->format($votacao->grupo) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Data') ?></dt>
                <dd class="col-sm-8"><?= $votacao->data ? h(date('d/m/Y', strtotime((string)$votacao->data))) : '' ?></dd>
            </dl>
            <?php if (!empty($votacao->item_modificada)) : ?>
                <div class="text">
                    <strong><?= __('Modificação/inclusão') ?></strong>
                    <blockquote>
                        <?= $this->Text->autoParagraph($votacao->item_modificada); ?>
                    </blockquote>
                </div>
            <?php endif; ?>
            <div class="text">
                <strong><?= __('Observações') ?></strong>
                <blockquote>
                    <?= $this->Text->autoParagraph(h($votacao->observacoes)); ?>
                </blockquote>
            </div>
        </div>
</div>
