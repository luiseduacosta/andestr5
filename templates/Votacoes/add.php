<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Votacao $votacao
 * @var \Cake\Collection\CollectionInterface|string[] $users
 * @var \Cake\Collection\CollectionInterface|string[] $eventos
 * @var \Cake\Collection\CollectionInterface|string[] $items
 */

$identity = $this->request->getAttribute('identity');
?>
<div class="row g-3">
        <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
            <ul class="navbar navbar-nav ms-auto mt-lg-0">
                <li class="nav-item"><?= $this->Html->link(__('List Votacoes'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
            </ul>
        </nav>
        <div class="card shadow-sm">
            <div class="card-body">
            <?= $this->Form->create($votacao, ['class' => 'needs-validation']) ?>
            <fieldset>
                <legend><?= __('Add Votacao') ?></legend>
                <?php
                    echo $this->Form->control('user_id', [
                        'options' => $users, 
                        'class' => 'form-select', 
                        'label' => ['class' => 'form-label'],
                        'value' => $identity ? $identity->id : null,
                    ]);
                    echo $this->Form->control('evento_id', ['options' => $eventos, 'class' => 'form-select', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('grupo', ['class' => 'form-control', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('tr', ['class' => 'form-control', 'label' => ['text' => 'TR', 'class' => 'form-label']]);
                    echo $this->Form->control('item_id', ['options' => $items, 'class' => 'form-select', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('item', ['type' => 'text', 'class' => 'form-control', 'label' => ['class' => 'form-label']]);
                    echo $this->Form->control('resultado', ['class' => 'form-control', 'label' => ['class' => 'form-label'], 'options' => [
                            'aprovada' => __('Aprovado'),
                            'suprimida' => __('Suprimida'),
                            'modificada' => __('Aprovado com modificações'),
                            'inclusao' => __('Inclusão de novo item'),
                            ],
                        ]);
                    echo $this->Form->control('votacao', ['class' => 'form-control', 'label' => ['text' => 'Votação', 'class' => 'form-label']]);
                    echo $this->Form->control('item_modificada', [
                        'class' => 'form-control markdown-editor', 
                        'label' => ['text' => 'Item Modificada', 'class' => 'form-label'],
                        'id' => 'item-modificada-field',
                        'style' => 'display: none;',
                    ]);
                    echo $this->Form->control('data', ['class' => 'form-control', 'label' => ['text' => 'Data', 'class' => 'form-label']]);
                    echo $this->Form->control('observacoes', ['class' => 'form-control markdown-editor', 'label' => ['text' => 'Observações', 'class' => 'form-label']]);
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
    
    const userIdSelect = document.querySelector('select[name="user_id"]');
    const grupoInput = document.querySelector('input[name="grupo"]');
    const itemIdSelect = document.querySelector('select[name="item_id"]');
    const itemInput = document.querySelector('input[name="item"]');
    const trInput = document.querySelector('input[name="tr"]');
    
    // User data passed from controller
    const usersData = <?= json_encode($usersData ?? []) ?>;
    
    // Item data - we'll get this from the item_id select options
    // The item text should be in the option text
    
    if (!userIdSelect || !grupoInput) {
        return;
    }
    
    function updateGrupoFromUser() {
        const selectedUserId = userIdSelect.value;
        
        if (!selectedUserId || !usersData[selectedUserId]) {
            return;
        }
        
        const userData = usersData[selectedUserId];
        
        // If user is a relator, extract grupo from username
        if (userData.role === 'relator') {
            // Extract number after 'grupo' prefix (e.g., 'grupo1' -> '1', 'grupo10' -> '10')
            const match = userData.username.match(/^grupo(\d+)$/i);
            if (match) {
                grupoInput.value = match[1];
            }
        } else {
            // Clear grupo for non-relator users
            grupoInput.value = '';
        }
    }
    
    function updateItemAndTrFromItemId() {
        if (!itemIdSelect || !itemInput || !trInput) {
            return;
        }
        
        const selectedItemId = itemIdSelect.value;
        
        if (!selectedItemId) {
            return;
        }
        
        // Get the selected option element
        const selectedOption = itemIdSelect.options[itemIdSelect.selectedIndex];
        const optionText = selectedOption.text.trim();
        
        // Extract the item part (after the dash)
        // Format: "id - item" -> we want only "item"
        let itemText = optionText;
        const dashMatch = optionText.match(/^[\d]+\s*-\s*(.+)$/);
        if (dashMatch) {
            itemText = dashMatch[1].trim();
        }
        
        // Set the item field to the extracted item text
        itemInput.value = itemText;
        
        // Extract first two digits from the item text for TR
        const trMatch = itemText.match(/^(\d{2})/);
        if (trMatch) {
            trInput.value = trMatch[1];
        }
    }
    
    // --- Toggle item_modificada visibility based on resultado ---
    const resultadoSelect = document.querySelector('select[name="resultado"]');
    const itemModificadaField = document.getElementById('item-modificada-field');
    const itemModificadaLabel = document.querySelector('label[for="item-modificada-field"]');
    
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
        } else {
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
    
    // Listen for user selection changes
    if (userIdSelect) {
        userIdSelect.addEventListener('change', updateGrupoFromUser);
        
        // Run on page load if a user is already selected
        if (userIdSelect.value) {
            updateGrupoFromUser();
        }
    }
    
    // Listen for item_id selection changes
    if (itemIdSelect) {
        itemIdSelect.addEventListener('change', updateItemAndTrFromItemId);
        
        // Run on page load if an item is already selected
        if (itemIdSelect.value) {
            updateItemAndTrFromItemId();
        }
    }
});
</script>
<?php $this->end(); ?>
