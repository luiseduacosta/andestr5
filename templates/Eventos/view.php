<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Evento $evento
 */

/**
 * Fix mojibake encoding issues (UTF-8 text stored as LATIN-1 or HTML entities)
 */
function fixEncoding($text) {
    if (empty($text)) {
        return $text;
    }    
    // First, decode HTML entities to actual characters
    $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    // Ensure string is valid UTF-8
    $decoded = mb_convert_encoding($decoded, 'UTF-8', 'UTF-8');

    // Target double-encoded UTF-8 sequences (using CP1252 and /u flag)
    $pattern = '/[\x{00C2}-\x{00DF}][\x{0080}-\x{00BF}€‚ƒ„…†‡ˆ‰Š‹ŒŽ‘’“”•–—˜™š›œžŸ]|[\x{00E0}-\x{00EF}][\x{0080}-\x{00BF}€‚ƒ„…†‡ˆ‰Š‹ŒŽ‘’“”•–—˜™š›œžŸ]{2}|[\x{00F0}-\x{00F4}][\x{0080}-\x{00BF}€‚ƒ„…†‡ˆ‰Š‹ŒŽ‘’“”•–—˜™š›œžŸ]{3}/u';
    
    return preg_replace_callback($pattern, function($matches) {
        return mb_convert_encoding($matches[0], 'Windows-1252', 'UTF-8');
    }, $decoded);
}
?>
<div class="row g-3">
        <?php $identity = $this->request->getAttribute('identity'); ?>
        <?php if (!$identity || ($identity->role !== 'relator')): ?>
        <nav class="navbar navbar-expand-lg navbar-light bg-light flex-column align-items-stretch p-3 rounded">
            <ul class="navbar navbar-nav ms-auto mt-lg-0">
                <li class="nav-item"><?= $this->Html->link(__('List Eventos'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary w-100']) ?></li>
                <li class="nav-item"><?= $this->Html->link(__('Edit Evento'), ['action' => 'edit', $evento->id], ['class' => 'btn btn-primary w-100']) ?></li>
                <li class="nav-item"><?= $this->Form->postLink(__('Delete Evento'), ['action' => 'delete', $evento->id], ['confirm' => __('Are you sure you want to delete # {0}?', $evento->id), 'class' => 'btn btn-outline-danger w-100']) ?></li>
                <li class="nav-item"><?= $this->Html->link(__('New Evento'), ['action' => 'add'], ['class' => 'btn btn-outline-primary w-100']) ?></li>
            </ul>
        </nav>
        <?php endif; ?>
        <div class="card shadow-sm">
            <div class="card-header">
                <h2 class="card-title"><?= h($evento->nome) ?></h2>
            </div>
            <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-4 text-secondary"><?= __('Nome') ?></dt>
                <dd class="col-sm-8"><?= h($evento->nome) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Data') ?></dt>
                <dd class="col-sm-8"><?= h($evento->data) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Local') ?></dt>
                <dd class="col-sm-8"><?= h($evento->local) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Id') ?></dt>
                <dd class="col-sm-8"><?= $this->Number->format($evento->id) ?></dd>
                <dt class="col-sm-4 text-secondary"><?= __('Ordem') ?></dt>
                <dd class="col-sm-8"><?= $this->Number->format($evento->ordem) ?></dd>
            </dl>
            <!--// Show only the id, numero texto, titulo, autor and a few linhas of the texto-->
            <div class="card mt-4">
                <h4><?= __('Apoios') ?></h4>
                <?php if (!empty($evento->apoios)) : ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= __('Id') ?></th>
                                <th><?= __('Numero') ?></th>
                                <th><?= __('Titulo') ?></th>
                                <th><?= __('Autor') ?></th>
                                <th><?= __('Texto') ?></th>
                                <th class="d-flex flex-wrap gap-2"><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($evento->apoios as $apoio) : ?>
                        <tr>
                            <td><?= h($apoio->id) ?></td>
                            <td><?= h($apoio->numero_texto) ?></td>
                            <td><?= h($apoio->titulo) ?></td>
                            <td>
                                <div class="autor-truncated">
                                    <?= mb_substr(fixEncoding($apoio->autor), 0, 300) ?><?= mb_strlen(fixEncoding($apoio->autor)) > 300 ? '...' : '' ?>
                                </div>
                                <?php if (mb_strlen(fixEncoding($apoio->autor)) > 300): ?>
                                    <button type="button" class="btn btn-sm btn-link p-0 mt-1 btn-expandir-texto" data-bs-toggle="modal" data-bs-target="#modalAutor<?= $apoio->id ?>">
                                        <?= __('Ver autor completo') ?>
                                    </button>
                                    <!-- Modal -->
                                    <div class="modal fade" id="modalAutor<?= $apoio->id ?>" tabindex="-1" aria-labelledby="modalAutorLabel<?= $apoio->id ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalAutorLabel<?= $apoio->id ?>">
                                                        <?= __('Autor Completo - Apoio #{0}', $apoio->id) ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?= nl2br(h(fixEncoding($apoio->autor))) ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('Fechar') ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="texto-truncated" data-full-text="<?= h(json_encode(fixEncoding($apoio->texto))) ?>">
                                    <?= mb_substr(fixEncoding($apoio->texto), 0, 300) ?><?= mb_strlen(fixEncoding($apoio->texto)) > 300 ? '...' : '' ?>
                                </div>
                                <?php if (mb_strlen(fixEncoding($apoio->texto)) > 300): ?>
                                    <button type="button" class="btn btn-sm btn-link p-0 mt-1 btn-expandir-texto" data-bs-toggle="modal" data-bs-target="#modalTexto<?= $apoio->id ?>">
                                        <?= __('Ver texto completo') ?>
                                    </button>
                                    <!-- Modal -->
                                    <div class="modal fade" id="modalTexto<?= $apoio->id ?>" tabindex="-1" aria-labelledby="modalTextoLabel<?= $apoio->id ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="modalTextoLabel<?= $apoio->id ?>">
                                                        <?= __('Texto Completo - Apoio #{0}', $apoio->id) ?>
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <?= nl2br(h(fixEncoding($apoio->texto))) ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('Fechar') ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="d-flex flex-wrap gap-2">
                                <?= $this->Html->link(__('View'), ['controller' => 'Apoios', 'action' => 'view', $apoio->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                <?php if (!$identity || ($identity->role !== 'relator')): ?>
                                    <?= $this->Html->link(__('Edit'), ['controller' => 'Apoios', 'action' => 'edit', $apoio->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Apoios', 'action' => 'delete', $apoio->id], ['confirm' => __('Are you sure you want to delete # {0}?', $apoio->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="card mt-4">
                <div class="card-header">
                    <h4><?= __('Votacoes') ?></h4>
                </div>
                <?php if (!empty($evento->votacoes)) : ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th><?= __('Id') ?></th>
                                <th><?= __('User Id') ?></th>
                                <th><?= __('Evento Id') ?></th>
                                <th><?= __('Grupo') ?></th>
                                <th><?= __('Tr') ?></th>
                                <th><?= __('Item Id') ?></th>
                                <th><?= __('Item') ?></th>
                                <th><?= __('Resultado') ?></th>
                                <th><?= __('Votacao') ?></th>
                                <th><?= __('Modificada') ?></th>
                                <th><?= __('Data') ?></th>
                                <th><?= __('Observacoes') ?></th>
                                <th class="d-flex flex-wrap gap-2"><?= __('Actions') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($evento->votacoes as $votacao) : ?>
                        <tr>
                            <td><?= h($votacao->id) ?></td>
                            <td><?= h($votacao->user_id) ?></td>
                            <td><?= h($votacao->evento_id) ?></td>
                            <td><?= h($votacao->grupo) ?></td>
                            <td><?= h($votacao->tr) ?></td>
                            <td><?= h($votacao->item_id) ?></td>
                            <td><?= h($votacao->item) ?></td>
                            <td><?= h($votacao->resultado) ?></td>
                            <td><?= h($votacao->votacao) ?></td>
                            <td><?= h($votacao->item_modificada) ?></td>
                            <td><?= h($votacao->data) ?></td>
                            <td><?= h($votacao->observacoes) ?></td>
                            <td class="d-flex flex-wrap gap-2">
                                <?= $this->Html->link(__('View'), ['controller' => 'Votacoes', 'action' => 'view', $votacao->id], ['class' => 'btn btn-sm btn-outline-primary']) ?>
                                <?php if (!$identity || ($identity->role !== 'relator')): ?>
                                    <?= $this->Html->link(__('Edit'), ['controller' => 'Votacoes', 'action' => 'edit', $votacao->id], ['class' => 'btn btn-sm btn-outline-secondary']) ?>
                                    <?= $this->Form->postLink(__('Delete'), ['controller' => 'Votacoes', 'action' => 'delete', $votacao->id], ['confirm' => __('Are you sure you want to delete # {0}?', $votacao->id), 'class' => 'btn btn-sm btn-outline-danger']) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
        </div>
        </div>
    </div>
</div>