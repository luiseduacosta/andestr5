<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ItemsFixture
 */
class ItemsFixture extends TestFixture
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
                'apoio_id' => 1,
                'tr' => 1,
                'item' => 'Item 1',
                'texto' => 'Texto Item 1',
                'user_id' => 1,
            ],
            [
                'id' => 2,
                'apoio_id' => 2,
                'tr' => 2,
                'item' => 'Item 2',
                'texto' => 'Texto Item 2',
                'user_id' => 1,
            ],
            [
                'id' => 3,
                'apoio_id' => 2,
                'tr' => 3,
                'item' => 'Item 3',
                'texto' => 'Texto Item 3',
                'user_id' => 1,
            ],
            [
                'id' => 4,
                'apoio_id' => 2,
                'tr' => 4,
                'item' => 'Item 99',
                'texto' => 'Texto Item 99 (Admin created)',
                'user_id' => 1,
            ],
            [
                'id' => 5,
                'apoio_id' => 2,
                'tr' => 5,
                'item' => 'Item 99',
                'texto' => 'Texto Item 99 (Relator created)',
                'user_id' => 3,
            ],
        ];
        parent::init();
    }
}
