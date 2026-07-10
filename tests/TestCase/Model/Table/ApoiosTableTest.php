<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ApoiosTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\ApoiosTable Test Case
 */
class ApoiosTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\ApoiosTable
     */
    protected $Apoios;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Apoios',
        'app.Eventos',
        'app.Items',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Apoios') ? [] : ['className' => ApoiosTable::class];
        $this->Apoios = $this->getTableLocator()->get('Apoios', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Apoios);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
