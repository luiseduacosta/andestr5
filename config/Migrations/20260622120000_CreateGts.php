<?php
declare(strict_types=1);

use Migrations\BaseMigration;
class CreateGts extends BaseMigration
{
    /**
     * Change Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/en/index.html
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('gts');
        $table->addColumn('sigla', 'string', [
            'default' => null,
            'limit' => 20,
            'null' => false,
        ]);
        $table->addColumn('nome', 'string', [
            'default' => null,
            'limit' => 100,
            'null' => true,
        ]);
        $table->addColumn('outras', 'string', [
            'default' => null,
            'limit' => 50,
            'null' => true,
        ]);
        $table->create();
    }
}
