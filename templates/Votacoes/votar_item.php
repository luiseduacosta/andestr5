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
                    <p><?= $item->texto ?></p>
                </div>
            </div>

            <?= $this->Form->create(null, ['url' => ['action' => 'votarItem', $item->id], 'class' => 'needs-validation']) ?>
            <fieldset>
                <legend><?= __('Registro da votação do item') ?></legend>
                <?php
                    echo $this->Form->control('resultado', [
                        'options' => [
                            'aprovada' => __('Aprovado'),
                            'suprimida' => __('Suprimida'),
                            'modificada' => __('Aprovado com modificações'),
                        ],
                        'class' => 'form-select',
                        'label' => ['class' => 'form-label', 'text' => __('Resultado')],
                        'empty' => false,
                    ]);
                    echo $this->Form->control('votacao', [
                        'class' => 'form-control',
                        'label' => ['class' => 'form-label', 'text' => __('Votação')],
                        'placeholder' => 'ex: 9/6/0',
                        'help' => __('Formato: favoráveis/contrários/abstenções'),
                    ]);
                    echo $this->Form->control('item_modificada', [
                        'type' => 'textarea',
                        'class' => 'form-control item-modificada-editor',
                        'label' => ['class' => 'form-label', 'text' => __('Modificação')],
                        'help' => __('Preencha apenas se houve modificação aprovada.'),
                        'id' => 'item-modificada-field',
                        'style' => 'display: none;',
                    ]);
                    echo $this->Form->control('destaque_minoria', [
                        'type' => 'checkbox',
                        'class' => 'form-check-input',
                        'id' => 'destaque-minoria-check',
                        'label' => ['class' => 'form-check-label', 'text' => __('Destaque de minoria (≥ 1/3 dos votantes)')],
                        'help' => __('Calculado automaticamente com base na votação.'),
                    ]);
                    echo $this->Html->div('destaque-minoria-alert', '', ['class' => 'alert alert-info mt-2', 'style' => 'display: none;']);
                    echo $this->Form->control('observacoes', [
                        'class' => 'form-control markdown-editor',
                        'label' => ['class' => 'form-label', 'text' => __('Observações adicionais (opcional)')],
                        'help' => __('Adicione informações adicionais sobre a votação, se necessário.'),
                    ]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Registrar Votação'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<?php $this->append('script'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // --- Toggle item_modificada visibility ---
    const textoOriginal = <?= json_encode(html_entity_decode($item->texto, ENT_QUOTES, 'UTF-8')) ?>;
    const resultadoSelect = document.querySelector('select[name="resultado"]');
    const itemModificadaField = document.getElementById('item-modificada-field');
    let easyMDE = null;
    
    function toggleItemModificada() {
        if (!resultadoSelect || !itemModificadaField) {
            return;
        }
        
        const wrapper = itemModificadaField.closest('.mb-3, .form-group') || itemModificadaField.parentElement;
        const showField = resultadoSelect.value === 'modificada';
        
        if (showField) {
            itemModificadaField.style.display = '';
            if (wrapper) {
                wrapper.style.display = '';
            }
            if (!easyMDE && typeof EasyMDE !== 'undefined') {
                easyMDE = new EasyMDE({
                    element: itemModificadaField,
                    spellChecker: false,
                    nativeSpellcheck: true,
                    forceSync: true,
                    autoDownloadFontAwesome: false,
                    toolbar: [
                        'bold', 'italic', {
                            name: 'strikethrough',
                            action: EasyMDE.toggleStrikethrough,
                            className: 'fa fa-strikethrough',
                            title: 'Strikethrough'
                        }, 'heading', '|',
                        'quote', 'unordered-list', 'ordered-list', '|',
                        'link', 'image', '|',
                        'preview', 'side-by-side', 'fullscreen', '|',
                        'guide'
                    ],
                    renderingConfig: {
                        singleLineBreaks: true,
                        codeSyntaxHighlighting: true,
                    },
                });
                if (!easyMDE.value().trim()) {
                    easyMDE.value(textoOriginal);
                }
            }
        } else {
            if (easyMDE) {
                easyMDE.toTextArea();
                easyMDE = null;
            }
            itemModificadaField.value = '';
            itemModificadaField.style.display = 'none';
            if (wrapper) {
                wrapper.style.display = 'none';
            }
        }
    }
    
    if (resultadoSelect) {
        resultadoSelect.addEventListener('change', toggleItemModificada);
        toggleItemModificada();
    }
    
    // --- Calculate destaque_minoria ---
    const votacaoInput = document.querySelector('input[name="votacao"]');
    const destaqueCheck = document.getElementById('destaque-minoria-check');
    const destaqueAlert = document.querySelector('.destaque-minoria-alert');
    
    if (!votacaoInput || !destaqueCheck || !destaqueAlert) {
        return;
    }
    
    function calcularDestaqueMinoria() {
        const votacao = votacaoInput.value.trim();
        
        // Parse format: X/Y/Z (favoráveis/contrários/abstenções)
        const parts = votacao.split('/').map(s => parseInt(s.trim(), 10));
        
        if (parts.length !== 3 || parts.some(n => isNaN(n) || n < 0)) {
            destaqueAlert.style.display = 'none';
            return;
        }
        
        const [favoraveis, contrarios, abstencoes] = parts;
        const totalVotos = favoraveis + contrarios + abstencoes;
        
        if (totalVotos === 0) {
            destaqueAlert.style.display = 'none';
            return;
        }
        
        // Losing side is the minority between favorable and contrary
        const ladoPerdedor = Math.min(favoraveis, contrarios);
        const umTerco = totalVotos / 3;
        
        if (ladoPerdedor >= umTerco) {
            destaqueCheck.checked = true;
            destaqueCheck.disabled = false;
            destaqueAlert.textContent = '✓ Destaque de minoritária ativado: ' + ladoPerdedor + ' de ' + totalVotos + ' votos (≥ ' + umTerco.toFixed(2) + ')';
            destaqueAlert.style.display = 'block';
        } else {
            destaqueCheck.checked = false;
            destaqueCheck.disabled = false;
            destaqueAlert.textContent = '✗ Votação minoritária tem ' + ladoPerdedor + ' votos (< ' + umTerco.toFixed(2) + ' necessário para destaque)';
            destaqueAlert.style.display = 'block';
        }
    }
    
    // Calculate on input change
    votacaoInput.addEventListener('input', calcularDestaqueMinoria);
    votacaoInput.addEventListener('change', calcularDestaqueMinoria);
    
    // Calculate on page load if value exists
    if (votacaoInput.value) {
        calcularDestaqueMinoria();
    }
});
</script>
<?php $this->end(); ?>
