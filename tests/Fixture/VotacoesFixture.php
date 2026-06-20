<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * VotacoesFixture
 */
class VotacoesFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'user_id' => 3,
                'evento_id' => 1,
                'grupo' => 1,
                'tr' => 1,
                'tr_suprimida' => 0,
                'tr_aprovada' => 1,
                'item_id' => 1,
                'item' => 'Item 1',
                'resultado' => 'Aprovado',
                'votacao' => 'Sim',
                'item_modificada' => 'Texto modificado 1',
                'data' => '2024-10-21 05:05:55',
                'observacoes' => 'Obs 1',
            ],
            [
                'id' => 2,
                'user_id' => 1,
                'evento_id' => 2,
                'grupo' => 2,
                'tr' => 2,
                'tr_suprimida' => 0,
                'tr_aprovada' => 1,
                'item_id' => 2,
                'item' => 'Item 2',
                'resultado' => 'Aprovado',
                'votacao' => 'Sim',
                'item_modificada' => 'Texto modificado 2',
                'data' => '2024-10-21 05:05:55',
                'observacoes' => 'Obs 2',
            ],
        ];
        parent::init();
    }
}
