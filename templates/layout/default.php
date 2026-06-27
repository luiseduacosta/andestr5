<?php
/**
 * @var \App\View\AppView $this
 */
?>
<!DOCTYPE html>
<html>
<head>
    <?= $this->Html->charset() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($this->fetch('title') ?: 'Andestr') ?></title>
    <?= $this->Html->meta('icon') ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <?= $this->Html->css('app') ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
</head>
<body class="bg-body-tertiary">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <?= $this->Html->link('Andestr', '/', ['class' => 'navbar-brand fw-semibold']) ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <?php $identity = $this->request->getAttribute('identity'); ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><?= $this->Html->link('Eventos', ['controller' => 'Eventos', 'action' => 'index'], ['class' => 'nav-link']) ?></li>
                    <li class="nav-item"><?= $this->Html->link('Apoios', ['controller' => 'Apoios', 'action' => 'index'], ['class' => 'nav-link']) ?></li>
                    <li class="nav-item"><?= $this->Html->link('Items', ['controller' => 'Items', 'action' => 'index'], ['class' => 'nav-link']) ?></li>
                    <li class="nav-item"><?= $this->Html->link('Votacoes', ['controller' => 'Votacoes', 'action' => 'index'], ['class' => 'nav-link']) ?></li>
                    <li class="nav-item"><?= $this->Html->link('Relatório', ['controller' => 'Votacoes', 'action' => 'report'], ['class' => 'nav-link']) ?></li>
                    <?php if (!$identity || ($identity->role !== 'relator')): ?>
                    <li class="nav-item"><?= $this->Html->link('Users', ['controller' => 'Users', 'action' => 'index'], ['class' => 'nav-link']) ?></li>
                    <?php endif; ?>
                </ul>
                <div class="d-flex align-items-center">
                    <?php if ($identity): ?>
                        <?php if ($identity->role === 'admin' || $identity->role === 'editor'): ?>
                            <?= $this->Form->create(null, [
                                'url' => ['controller' => 'Eventos', 'action' => 'select'],
                                'class' => 'd-flex align-items-center me-3',
                                'id' => 'select-evento-form'
                            ]) ?>
                                <span class="text-white me-2 small text-nowrap"><?= __('Evento Ativo:') ?></span>
                                <?= $this->Form->control('evento_id', [
                                    'type' => 'select',
                                    'options' => $allEventos,
                                    'value' => $selectedEvento ? $selectedEvento->id : null,
                                    'label' => false,
                                    'class' => 'form-select form-select-sm',
                                    'onchange' => 'document.getElementById("select-evento-form").submit()'
                                ]) ?>
                            <?= $this->Form->end() ?>
                        <?php else: ?>
                            <span class="text-white me-3 small text-nowrap">
                                <?= __('Evento Ativo:') ?> <strong><?= $selectedEvento ? h($selectedEvento->nome) : __('Nenhum') ?></strong>
                            </span>
                        <?php endif; ?>

                        <span class="navbar-text me-3 text-white small text-nowrap">
                            <?= h($identity->username) ?> (<?= h($identity->role) ?>)
                        </span>
                        <?= $this->Html->link('Logout', ['controller' => 'Users', 'action' => 'logout'], ['class' => 'btn btn-outline-light btn-sm']) ?>
                    <?php else: ?>
                        <?= $this->Html->link('Login', ['controller' => 'Users', 'action' => 'login'], ['class' => 'btn btn-light btn-sm']) ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="py-4 py-lg-5">
        <div class="container">
            <?= $this->Flash->render() ?>
            <?= $this->fetch('content') ?>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.js"></script>
    <?= $this->Html->script('app') ?>
    <?= $this->fetch('script') ?>
</body>
</html>
