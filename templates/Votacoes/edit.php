<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Votacao $votacao
 * @var string[]|\Cake\Collection\CollectionInterface $users
 * @var string[]|\Cake\Collection\CollectionInterface $eventos
 * @var string[]|\Cake\Collection\CollectionInterface $items
 */
?>
<div class="row g-3">
        <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
            <ul class="navbar-nav ms-auto mt-lg-0">
                <li class="nav-item"><?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $votacao->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $votacao->id), 'class' => 'btn btn-outline-danger w-100']
            ) ?></li>
                <li class="nav-item"><?= $this->Html->link(__('List Votacoes'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
            </ul>
        </nav>
        <div class="card shadow-sm">
            <div class="card-body">
            <?= $this->Form->create($votacao, ['class' => 'needs-validation']) ?>
            <fieldset>
                <legend><?= __('Edit Votacao') ?></legend>
                <?php
                    echo $this->Form->control('user_id', ['options' => $users, 'class' => 'form-select', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('evento_id', ['options' => $eventos, 'class' => 'form-select', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('grupo', ['class' => 'form-control', 'label' => ['class' => 'form-label', 'text' => __('Grupo') . '']]);
                    echo $this->Form->control('tr', ['class' => 'form-control', 'label' => ['class' => 'form-label', 'text' => __('TR') . '']]);
                    echo $this->Form->control('item_id', ['options' => $items, 'class' => 'form-select', 'label' => ['class' => 'form-label', 'text' => __('Item Id') . '']]);
                    echo $this->Form->control('item', ['type' => 'text', 'class' => 'form-control', 'label' => ['class' => 'form-label', 'text' => __('Item') . '']]);
                    echo $this->Form->control('resultado', ['class' => 'form-control', 'label' => ['class' => 'form-label', 'text' => __('Resultado') . ''], 'options' => [
                        'suprimida' => 'Suprimida', 
                        'aprovada' => 'Aprovada', 
                        'modificada' => 'Modificada']
                    ]);
                    echo $this->Form->control('votacao', ['class' => 'form-control', 'label' => ['class' => 'form-label', 'text' => __('Votação') . '']]);
                    echo $this->Form->control('item_modificada', [
                        'class' => 'form-control markdown-editor', 
                        'label' => ['class' => 'form-label', 'text' => __('Modificação/inclusão') . ''],
                        'id' => 'item-modificada-field',
                        'style' => 'display: none;',
                    ]);
                    echo $this->Form->control('data', ['class' => 'form-control', 'label' => ['class' => 'form-label', 'text' => __('Data') . '']]);
                    echo $this->Form->control('observacoes', ['class' => 'form-control markdown-editor', 'label' => ['class' => 'form-label', 'text' => __('Observações') . '']]);
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit'), ['class' => 'btn btn-primary']) ?>
            <?= $this->Form->end() ?>
            </div>
        </div>
</div>

<?php $this->append('script'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    const resultadoSelect = document.querySelector('select[name="resultado"]');
    const itemModificadaField = document.getElementById('item-modificada-field');
    const itemModificadaLabel = document.querySelector('label[for="item-modificada-field"]');
    const itemIdSelect = document.querySelector('select[name="item_id"]');
    
    // Item texts passed from controller
    const itemTexts = <?= json_encode($itemTexts ?? []) ?>;
    
    if (!resultadoSelect || !itemModificadaField) {
        return;
    }
    
    function toggleItemModificada() {
        if (!resultadoSelect || !itemModificadaField) {
            return;
        }
        
        const wrapper = itemModificadaField.closest('.mb-3, .form-group') || itemModificadaField.parentElement;
        const resultadoValue = resultadoSelect.value;
        const showField = resultadoValue === 'modificada' || resultadoValue === 'inclusao';
        
        if (showField) {
            itemModificadaField.style.display = '';
            if (wrapper) {
                wrapper.style.display = '';
            }
            
            // Change label based on resultado
            if (itemModificadaLabel) {
                if (resultadoValue === 'inclusao') {
                    itemModificadaLabel.textContent = 'Inclusão de novo item';
                } else {
                    itemModificadaLabel.textContent = 'Item Modificada';
                }
            }
            
            // Auto-fill item_modificada with item text when resultado is 'modificada'
            if (resultadoValue === 'modificada' && itemIdSelect && itemTexts) {
                const selectedItemId = itemIdSelect.value;
                if (selectedItemId && itemTexts[selectedItemId]) {
                    // Only auto-fill if field is empty or user hasn't modified it
                    if (!itemModificadaField.value || itemModificadaField.dataset.autoFilled === 'true') {
                        itemModificadaField.value = itemTexts[selectedItemId];
                        itemModificadaField.dataset.autoFilled = 'true';
                    }
                }
            }
        } else {
            itemModificadaField.value = '';
            itemModificadaField.style.display = 'none';
            itemModificadaField.dataset.autoFilled = 'false';
            if (wrapper) {
                wrapper.style.display = 'none';
            }
        }
    }
    
    // Listen for resultado changes
    if (resultadoSelect) {
        resultadoSelect.addEventListener('change', toggleItemModificada);
        toggleItemModificada();
    }
    
    // Listen for item_id changes to update item_modificada when resultado is 'modificada'
    if (itemIdSelect) {
        itemIdSelect.addEventListener('change', function() {
            if (resultadoSelect && resultadoSelect.value === 'modificada') {
                const selectedItemId = this.value;
                if (selectedItemId && itemTexts[selectedItemId]) {
                    itemModificadaField.value = itemTexts[selectedItemId];
                    itemModificadaField.dataset.autoFilled = 'true';
                }
            }
        });
    }
    
    // Mark as manually modified if user types in the field
    if (itemModificadaField) {
        itemModificadaField.addEventListener('input', function() {
            this.dataset.autoFilled = 'false';
        });
    }
});
</script>
<?php $this->end(); ?>
