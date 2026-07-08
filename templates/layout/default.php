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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/easymde/dist/easymde.min.css">
    <!-- Custom styles to ensure EasyMDE toolbar visibility -->
    <style>
        /* Ensure the EasyMDE wrapper and toolbar are visible */
        .EasyMDEContainer {
            display: block !important;
            width: 100%;
        }

        .EasyMDEContainer .editor-toolbar {
            background-color: #f8f9fa !important;
            border: 1px solid #ced4da !important;
            border-bottom: none !important;
            padding: 8px !important;
            display: flex !important;
            flex-wrap: wrap !important;
        }

        .EasyMDEContainer .editor-toolbar button {
            color: #333333 !important;
            background-color: transparent !important;
            border: none !important;
        }

        .EasyMDEContainer .editor-toolbar button:hover {
            background-color: #e9ecef !important;
        }

        .EasyMDEContainer .editor-toolbar .separator {
            border-left: 1px solid #dee2e6 !important;
        }

        .EasyMDEContainer .CodeMirror {
            border: 1px solid #ced4da !important;
        }

        /* Fix for FontAwesome icons if not loading */
        .editor-toolbar i {
            font-family: FontAwesome, sans-serif !important;
            font-style: normal !important;
        }
    </style>
    <?= $this->fetch('meta') ?>
    <?= $this->fetch('css') ?>
</head>
<body class="bg-body-tertiary">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
        <div class="container">
            <?= $this->Html->link('Andes-SN Votações', '/', ['class' => 'navbar-brand fw-semibold']) ?>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="mainNav">
                <?php $identity = $this->request->getAttribute('identity'); ?>
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <?php if (!$identity || ($identity->role !== 'relator')): ?>
                    <li class="nav-item"><?= $this->Html->link('Eventos', ['controller' => 'Eventos', 'action' => 'index'], ['class' => 'nav-link']) ?></li>
                    <?php endif; ?>
                    <li class="nav-item"><?= $this->Html->link('Apoios', ['controller' => 'Apoios', 'action' => 'index'], ['class' => 'nav-link']) ?></li>
                    <li class="nav-item"><?= $this->Html->link('Items', ['controller' => 'Items', 'action' => 'index'], ['class' => 'nav-link']) ?></li>
                    <li class="nav-item"><?= $this->Html->link('Votacoes', ['controller' => 'Votacoes', 'action' => 'index'], ['class' => 'nav-link']) ?></li>
                    <li class="nav-item"><?= $this->Html->link('Relatório', ['controller' => 'Votacoes', 'action' => 'relatorio'], ['class' => 'nav-link']) ?></li>
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
            <?php
                $session = $this->request->getSession();
                $impersonatedBy = $session->read('impersonated_by');
            ?>
            <?php if ($impersonatedBy): ?>
                <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center justify-content-between" role="alert">
                    <span>
                        <i class="fa fa-exclamation-triangle me-2"></i>
                        <?= __('You are currently impersonating another user.') ?>
                    </span>
                    <?= $this->Html->link(
                        __('Stop Impersonating'),
                        ['controller' => 'Users', 'action' => 'stopImpersonate'],
                        ['class' => 'btn btn-warning btn-sm fw-semibold']
                    ) ?>
                </div>
            <?php endif; ?>
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
