<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Votacao> $votacoes
 * @var string|null $trInput
 * @var array<int> $trList
 */

// Group votes by TR and then by item.
$groupedItems = [];
if (!empty($votacoes)) {
    foreach ($votacoes as $votacao) {
        $tr = (int)$votacao->tr;
        $itemKey = (string)($votacao->item_id ?: $votacao->item ?: uniqid('item_', true));

        if (!isset($groupedItems[$tr][$itemKey])) {
            $groupedItems[$tr][$itemKey] = [
                'codigo' => $votacao->item ?: ($votacao->votacao_item->item ?? ''),
                'texto' => $votacao->votacao_item->texto ?? '',
                'isAdded' => !empty($votacao->item) && str_ends_with($votacao->item, '99'),
                'votes' => [],
            ];
        }

        $groupedItems[$tr][$itemKey]['votes'][] = $votacao;
    }
}
?>
<div class="row">
    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0 bg-gradient-primary text-white p-4 rounded-4" style="background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);">
            <h2 class="mb-1 fw-bold"><?= __('Relatório de Votações por TR') ?></h2>
            <p class="mb-0 text-white-50"><?= __('Consolidação de itens, propostas de modificações e votos dos grupos de trabalho') ?></p>
        </div>
    </div>

    <div class="col-12 mb-4">
        <div class="card shadow-sm border-0 p-3 rounded-4">
            <?= $this->Form->create(null, ['type' => 'get', 'class' => 'needs-validation']) ?>
                <div class="row g-2 align-items-end">
                    <div class="col-lg-8 col-md-7 col-12">
                        <label for="trs-input" class="form-label fw-medium small mb-1"><?= __('Número(s) da(s) TR(s)') ?></label>
                        <?= $this->Form->control('trs', [
                            'type' => 'text',
                            'value' => $trInput,
                            'id' => 'trs-input',
                            'class' => 'form-control rounded-3',
                            'placeholder' => 'Ex: 1, 2, 5',
                            'label' => false,
                            'required' => true
                        ]) ?>
                    </div>
                    <div class="col-lg-2 col-md-3 col-6">
                        <label class="form-label small mb-1">&nbsp;</label>
                        <button type="submit" class="btn btn-primary rounded-3 shadow-sm w-100 d-flex align-items-center justify-content-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-text me-1" viewBox="0 0 16 16">
                                <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                                <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z"/>
                            </svg>
                            <?= __('Gerar') ?>
                        </button>
                    </div>
                    <div class="col-lg-2 col-md-2 col-6 d-flex align-items-end">
                        <div class="form-text text-muted small mb-0">
                            <?= __('Insira números separados por vírgula') ?>
                        </div>
                    </div>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>

    <div class="col-12 mb-4">
        <?php if (!empty($trList) && sizeof($votacoes) > 0): ?>
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h4 class="fw-semibold mb-0"><?= __('Resultado da Compilação') ?></h4>
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <?= $this->Html->link(
                        '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-download me-1.5" viewBox="0 0 16 16">' .
                        '<path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5"/>' .
                        '<path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708z"/>' .
                        '</svg>' . __('Baixar Markdown'),
                        ['action' => 'relatorio', '?' => ['trs' => $trInput, 'download' => 'markdown']],
                        ['class' => 'btn btn-outline-primary btn-sm rounded-pill shadow-sm d-flex align-items-center px-3 py-1.5', 'escape' => false]
                    ) ?>
                    <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill mb-0">
                        <?= __('TRs Consultadas: {0}', implode(', ', $trList)) ?>
                    </span>
                </div>
            </div>
            <?php foreach ($trList as $trNum): ?>
                <div class="card shadow-sm border-0 mb-4 rounded-4 overflow-hidden">
                    <div class="card-header bg-light border-0 py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold text-dark">TR <?= h($trNum) ?></h5>
                    </div>
                    <div class="card-body p-4">
                        <?php if (empty($groupedItems[$trNum])): ?>
                            <div class="alert alert-warning border-0 rounded-3 d-flex align-items-center mb-0" role="alert">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-exclamation-triangle-fill me-2" viewBox="0 0 16 16">
                                    <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5m.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2"/>
                                </svg>
                                <div>
                                    <?= __('Nenhuma votação registrada para a TR {0} no evento ativo.', h($trNum)) ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($groupedItems[$trNum] as $itemData): ?>
                                <?php
                                    $hasInclusaoVote = false;
                                    $inclusaoTexto = $itemData['texto'];
                                    foreach ($itemData['votes'] as $vote) {
                                        if (strtolower((string)$vote->resultado) === 'inclusão') {
                                            $hasInclusaoVote = true;
                                            if (!empty($vote->item_modificada)) {
                                                $inclusaoTexto = $vote->item_modificada;
                                            }
                                            break;
                                        }
                                    }
                                ?>
                                <div class="mb-4 pb-4 border-bottom last-border-0">
                                    <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                                        <h6 class="fw-bold mb-0 text-primary d-flex align-items-center">
                                            <span class="me-2">Item <?= h($itemData['codigo']) ?></span>
                                            <?php if ($itemData['isAdded']): ?>
                                                <span class="badge bg-warning text-dark small py-1 px-2 rounded"><?= __('Item Adicionado') ?></span>
                                            <?php endif; ?>
                                        </h6>
                                    </div>
                                    
                                    <?php if (!$hasInclusaoVote): ?>
                                        <div class="p-3 bg-light rounded-3 mb-3 text-secondary border-start border-primary border-3">
                                            <p class="mb-0 fs-6"><?= nl2br(h($itemData['texto'])) ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (empty($itemData['votes'])): ?>
                                        <p class="text-muted small italic mb-0"><?= __('Sem votações registradas por nenhum grupo.') ?></p>
                                    <?php else: ?>
                                        <div class="table-responsive rounded-3 border">
                                            <table class="table table-hover align-middle mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width: 15%;"><?= __('Grupo') ?></th>
                                                        <th style="width: 25%;"><?= __('Voto') ?></th>
                                                        <th style="width: 25%;"><?= __('Resultado') ?></th>
                                                        <th><?= __('Relator') ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($itemData['votes'] as $vote): ?>
                                                        <tr>
                                                            <td><span class="fw-bold">G<?= h($vote->grupo) ?></span></td>
                                                            <td>
                                                                <span class="badge <?= $vote->votacao === 'Sim' ? 'bg-success' : ($vote->votacao === 'Não' ? 'bg-danger' : 'bg-secondary') ?> py-1.5 px-2.5">
                                                                    <?= h($vote->votacao) ?>
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <span class="fw-semibold text-secondary">
                                                                    <?= h($vote->resultado) ?>
                                                                </span> 
                                                                <?php if ($vote->destaque_minoria): ?>
                                                                    <span class="badge bg-warning text-dark ms-1"><?= __('⚠ Destaque de Minoria') ?></span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td><small class="text-muted"><?= h($vote->user->username) ?></small></td>
                                                        </tr>
                                                        <?php if (!$hasInclusaoVote && !empty($vote->item_modificada)): ?>
                                                            <tr class="table-warning-subtle">
                                                                <td colspan="4" class="py-2 px-3">
                                                                    <div class="d-flex align-items-start">
                                                                        <span class="badge bg-warning text-dark me-2 small"><?= __('Modificação Proposta') ?></span>
                                                                        <div class="text-dark-emphasis small">
                                                                            <?= nl2br(h($vote->item_modificada)) ?>
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        <?php endif; ?>
                                                    <?php endforeach; ?>
                                                    <?php if ($hasInclusaoVote && !empty($inclusaoTexto)): ?>
                                                        <tr class="table-info-subtle">
                                                            <td colspan="4" class="py-3 px-3">
                                                                <div class="text-dark-emphasis small">
                                                                    <?= nl2br(h($inclusaoTexto)) ?>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="card shadow-sm border-0 p-5 rounded-4 text-center">
                <div class="my-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-file-earmark-bar-graph text-muted" viewBox="0 0 16 16">
                        <path d="M10 13.5a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-6a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5zm-2.5.5a.5.5 0 0 0 .5-.5v-4a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v4a.5.5 0 0 0 .5.5zm-3 0a.5.5 0 0 0 .5-.5v-2a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v2a.5.5 0 0 0 .5.5z"/>
                        <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
                    </svg>
                </div>
                <h5 class="fw-semibold"><?= __('TR sem votações no(s) grupo {0}', h(implode(', ', $trList))) ?></h5>
                <p class="text-muted mb-0"><?= __('Por favor, insira um ou mais números de TR no campo acima para gerar a compilação dos votos.') ?></p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.last-border-0:last-child {
    border-bottom: 0 !important;
    padding-bottom: 0 !important;
    margin-bottom: 0 !important;
}
</style>
