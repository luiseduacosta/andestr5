<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
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
                'username' => 'admin',
                'password' => '$2y$10$xyz', // Dummy hashed pwd
                'role' => 'admin',
                'created' => '2026-06-19 12:00:00',
                'modified' => '2026-06-19 12:00:00',
            ],
            [
                'id' => 2,
                'username' => 'editor',
                'password' => '$2y$10$xyz',
                'role' => 'editor',
                'created' => '2026-06-19 12:00:00',
                'modified' => '2026-06-19 12:00:00',
            ],
            [
                'id' => 3,
                'username' => 'relator',
                'password' => '$2y$10$xyz',
                'role' => 'relator',
                'created' => '2026-06-19 12:00:00',
                'modified' => '2026-06-19 12:00:00',
            ],
        ];
        parent::init();
    }
}
