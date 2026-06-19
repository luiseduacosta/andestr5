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
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item"><?= $this->Html->link('Eventos', ['controller' => 'Eventos', 'action' => 'index'], ['class' => 'nav-link']) ?></li>
                    <li class="nav-item"><?= $this->Html->link('Apoios', ['controller' => 'Apoios', 'action' => 'index'], ['class' => 'nav-link']) ?></li>
                    <li class="nav-item"><?= $this->Html->link('Items', ['controller' => 'Items', 'action' => 'index'], ['class' => 'nav-link']) ?></li>
                    <li class="nav-item"><?= $this->Html->link('Votacoes', ['controller' => 'Votacoes', 'action' => 'index'], ['class' => 'nav-link']) ?></li>
                    <li class="nav-item"><?= $this->Html->link('Users', ['controller' => 'Users', 'action' => 'index'], ['class' => 'nav-link']) ?></li>
                </ul>
                <div class="d-flex">
                    <?= $this->Html->link('Login', ['controller' => 'Users', 'action' => 'login'], ['class' => 'btn btn-light']) ?>
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
    <?= $this->fetch('script') ?>
</body>
</html>
