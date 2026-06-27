<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\ItemsController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use App\Model\Entity\User;

/**
 * App\Controller\ItemsController Test Case
 *
 * @uses \App\Controller\ItemsController
 */
class ItemsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Items',
        'app.Apoios',
        'app.Eventos',
        'app.Users',
        'app.Votacoes',
    ];

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\ItemsController::index()
     */
    public function testIndex(): void
    {
        // 1. Admin Role tests
        $admin = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        $this->session(['Auth' => $admin]);

        // Default event is last event: event 2.
        // Index should contain Item 2 (has vote), Item 3 (no vote), Item 4 (ending with 99) and Item 5 (ending with 99).
        $this->get('/items');
        $this->assertResponseOk();
        $this->assertResponseContains('Item 2');
        $this->assertResponseContains('Item 3');
        $this->assertResponseContains('/items/view/4');
        $this->assertResponseContains('/items/view/5');
        $this->assertResponseNotContains('Item 1');

        // Verify button for Item 2 (has vote): says 'view' and links to /votacoes?item_id=2
        $this->assertResponseContains('/votacoes?item_id=2');
        $this->assertResponseContains('view');
        // Verify button for Item 3 (no vote): says 'Sem votação' and links to /items/view/3
        $this->assertResponseContains('/items/view/3');
        $this->assertResponseContains('Sem votação');

        // Set selected event to 1 in session.
        $this->session([
            'Auth' => $admin,
            'selected_evento_id' => 1
        ]);
        $this->get('/items');
        $this->assertResponseOk();
        $this->assertResponseContains('Item 1');
        $this->assertResponseNotContains('Item 2');

        // Verify button for Item 1 (has vote): says 'view' and links to /votacoes?item_id=1
        $this->assertResponseContains('/votacoes?item_id=1');


        // 2. Relator Role tests
        $relator = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'relator'
        ]);
        
        // Event 2: Item 2 has a vote by admin (user 1), but not by relator (user 3).
        // It also contains Item 5 (relator created ending with 99), but NOT Item 4 (admin created ending with 99).
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 2
        ]);
        $this->get('/items');
        $this->assertResponseOk();
        
        $this->assertResponseContains('Item 2');
        $this->assertResponseContains('Item 3');
        $this->assertResponseContains('/items/view/5');
        $this->assertResponseNotContains('/items/view/4');

        // Verify button for Item 2 (no vote by relator): says 'Sem votação' and links to /items/view/2
        $this->assertResponseContains('/items/view/2');
        $this->assertResponseContains('Sem votação');

        // Event 1: Item 1 has a vote by user 3 (relator).
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 1
        ]);
        $this->get('/items');
        $this->assertResponseOk();

        // Verify button for Item 1 (has vote by relator): says 'View' (Capital V) and links to /votacoes/view/1
        $this->assertResponseContains('/votacoes/view/1');
        $this->assertResponseContains('View');
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\ItemsController::view()
     */
    public function testView(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\ItemsController::add()
     */
    public function testAdd(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\ItemsController::edit()
     */
    public function testEdit(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test delete method
     *
     * @return void
     * @uses \App\Controller\ItemsController::delete()
     */
    public function testDelete(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
