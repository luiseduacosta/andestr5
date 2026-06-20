<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * EventosFixture
 */
class EventosFixture extends TestFixture
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
                'ordem' => 1,
                'nome' => 'Evento 1',
                'data' => '2026-06-19',
                'local' => 'Local 1',
            ],
            [
                'id' => 2,
                'ordem' => 2,
                'nome' => 'Evento 2',
                'data' => '2026-06-20',
                'local' => 'Local 2',
            ],
        ];
        parent::init();
    }
}
