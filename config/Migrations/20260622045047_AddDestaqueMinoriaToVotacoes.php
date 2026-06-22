<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddDestaqueMinoriaToVotacoes extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/4/en/migrations.html#the-change-method
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('votacoes');
        $table->addColumn('destaque_minoria', 'boolean', [
            'default' => false,
            'null' => false,
        ]);
        $table->update();
    }
}
