<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

class GtsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            ['id' => 1, 'sigla' => 'GT1', 'nome' => 'Grupo de Trabalho 1', 'outras' => 'Outras 1'],
            ['id' => 2, 'sigla' => 'GT2', 'nome' => 'Grupo de Trabalho 2', 'outras' => 'Outras 2'],
        ];
        parent::init();
    }
}
