<?php
/**
 * @var \App\View\AppView $this
 * @var iterable<\App\Model\Entity\Item> $items
 * @var string|null $trInput
 * @var array<int> $trList
 */

// Group items by TR
$groupedItems = [];
if (!empty($items)) {
    foreach ($items as $item) {
        $groupedItems[$item->tr][] = $item;
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
</div>

<div class="row">
    <div class="col-lg-4 col-12 mb-4">
        <div class="card shadow-sm border-0 p-4 rounded-4">
            <h4 class="fw-semibold mb-3"><?= __('Configuração do Relatório') ?></h4>
            <?= $this->Form->create(null, ['type' => 'get', 'class' => 'needs-validation']) ?>
                <div class="mb-3">
                    <label for="trs-input" class="form-label fw-medium"><?= __('Número(s) da(s) TR(s)') ?></label>
                    <?= $this->Form->control('trs', [
                        'type' => 'text',
                        'value' => $trInput,
                        'id' => 'trs-input',
                        'class' => 'form-control form-control-lg rounded-3',
                        'placeholder' => 'Ex: 1, 2, 5',
                        'label' => false,
                        'required' => true
                    ]) ?>
                    <div class="form-text text-muted mt-2">
                        <?= __('Insira os números das TRs desejadas separados por vírgula para compilar os resultados.') ?>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg rounded-3 shadow-sm d-flex align-items-center justify-content-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-file-earmark-text me-2" viewBox="0 0 16 16">
                            <path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5m0 2a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5"/>
                            <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5z"/>
                        </svg>
                        <?= __('Gerar Relatório') ?>
                    </button>
                </div>
            <?= $this->Form->end() ?>
        </div>
    </div>

    <div class="col-lg-8 col-12 mb-4">
        <?php if (!empty($trList)): ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="fw-semibold mb-0"><?= __('Resultado da Compilação') ?></h4>
                <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill">
                    <?= __('TRs Consultadas: {0}', implode(', ', $trList)) ?>
                </span>
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
                                    <?= __('Nenhum item cadastrado para a TR {0} no evento ativo.', h($trNum)) ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($groupedItems[$trNum] as $item): ?>
                                <?php $isAdded = str_ends_with($item->item, '99'); ?>
                                <div class="mb-4 pb-4 border-bottom last-border-0">
                                    <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
                                        <h6 class="fw-bold mb-0 text-primary d-flex align-items-center">
                                            <span class="me-2">Item <?= h($item->item) ?></span>
                                            <?php if ($isAdded): ?>
                                                <span class="badge bg-warning text-dark small py-1 px-2 rounded"><?= __('Item Adicionado') ?></span>
                                            <?php endif; ?>
                                        </h6>
                                    </div>
                                    
                                    <div class="p-3 bg-light rounded-3 mb-3 text-secondary border-start border-primary border-3">
                                        <p class="mb-0 fs-6"><?= nl2br(h($item->texto)) ?></p>
                                    </div>

                                    <?php if (empty($item->votacoes)): ?>
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
                                                    <?php foreach ($item->votacoes as $vote): ?>
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
                                                            </td>
                                                            <td><small class="text-muted"><?= h($vote->user->username) ?></small></td>
                                                        </tr>
                                                        <?php if (!empty($vote->item_modificada)): ?>
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
                <h5 class="fw-semibold"><?= __('Nenhuma TR selecionada') ?></h5>
                <p class="text-muted mb-0"><?= __('Por favor, insira um ou mais números de TR na barra lateral para gerar a compilação dos votos.') ?></p>
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
