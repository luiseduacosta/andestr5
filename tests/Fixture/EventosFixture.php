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
                'nome' => 'Lorem ipsum dolor sit a',
                'data' => 'Lorem ipsum dolor sit amet',
                'local' => 'Lorem ipsum dolor sit a',
            ],
        ];
        parent::init();
    }
}
