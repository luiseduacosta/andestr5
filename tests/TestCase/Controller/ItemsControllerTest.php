<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\ItemsController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use App\Model\Entity\User;

/**
 * App\Controller\ItemsController Test Case
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
            'username' => 'grupo1'
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
     */
    public function testView(): void
    {
        $relator = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);

        // 1. Relator can view regular items
        $this->session(['Auth' => $relator]);
        $this->get('/items/view/2');
        $this->assertResponseOk();
        $this->assertResponseContains('Item 2');

        // 2. Relator can view their own .99 items (Item 5 is owned by user 3)
        $this->session(['Auth' => $relator]);
        $this->get('/items/view/5');
        $this->assertResponseOk();
        $this->assertResponseContains('05.99');

        // 3. Relator cannot view another user's .99 items (Item 4 is owned by user 1)
        $this->session(['Auth' => $relator]);
        $this->get('/items/view/4');
        $this->assertResponseCode(403); // Forbidden
    }

    /**
     * Test add method
     *
     * @return void
     */
    public function testAdd(): void
    {
        $relator = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);

        $this->session(['Auth' => $relator, 'selected_evento_id' => 2]);
        $this->get('/items/add');
        $this->assertResponseOk();

        // Test pre-selection and nextItemValue default calculation when items_tr_filter is set to 2 in session
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 2,
            'items_tr_filter' => 2
        ]);
        $this->get('/items/add');
        $this->assertResponseOk();
        $this->assertEquals(2, $this->viewVariable('item')->apoio_id);
        $this->assertEquals('02.01', $this->viewVariable('nextItemValue'));

        // Insert an item with '02.05' format to test nextItemValue increments to '02.06'
        $itemsTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Items');
        $itemMock = $itemsTable->newEntity([
            'apoio_id' => 2,
            'tr' => 2,
            'item' => '02.05',
            'texto' => 'Some existing item text',
            'user_id' => 1
        ]);
        $itemsTable->save($itemMock);

        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 2,
            'items_tr_filter' => 2
        ]);
        $this->get('/items/add');
        $this->assertResponseOk();
        $this->assertEquals('02.06', $this->viewVariable('nextItemValue'));

        // Submit valid item (should redirect to view/6 since 5 items are in fixture)
        $data = [
            'apoio_id' => 2,
            'tr' => 2,
            'item' => '02.99',
            'texto' => 'New inclusion item text',
        ];
        $this->session(['Auth' => $relator, 'selected_evento_id' => 2]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/items/add', $data);
        $this->assertRedirect(['action' => 'view', 7]);

        // Submit item with mismatched TR (apoio 2 has numero_texto = 2, we submit tr = 3)
        $mismatchedTrData = [
            'apoio_id' => 2,
            'tr' => 3,
            'item' => '02.99',
            'texto' => 'New inclusion item text',
        ];
        $this->session(['Auth' => $relator, 'selected_evento_id' => 2]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/items/add', $mismatchedTrData);
        $this->assertResponseOk();
        $this->assertResponseContains('Apoios.numero_texto deve ser igual ao Item.tr.');

        // Submit item with mismatched item prefix (apoio 2 has numero_texto = 2, we submit item prefix = 03)
        $mismatchedPrefixData = [
            'apoio_id' => 2,
            'tr' => 2,
            'item' => '03.99',
            'texto' => 'New inclusion item text',
        ];
        $this->session(['Auth' => $relator, 'selected_evento_id' => 2]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/items/add', $mismatchedPrefixData);
        $this->assertResponseOk();
        $this->assertResponseContains('Os dois primeiros dígitos do item devem ser iguais a Apoios.numero_texto (02).');

        // Submit item with mismatched selected_evento_id (selected event 1, but support 2 belongs to event 2)
        $mismatchedEventData = [
            'apoio_id' => 2,
            'tr' => 2,
            'item' => '02.99',
            'texto' => 'New inclusion item text',
        ];
        $this->session(['Auth' => $relator, 'selected_evento_id' => 1]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/items/add', $mismatchedEventData);
        $this->assertResponseOk();
        $this->assertResponseContains('O apoio não pertence ao evento selecionado.');

        // Check validation error detailed reporting
        $invalidData = [
            'apoio_id' => 2,
            'tr' => 2,
            'item' => 'way-too-long-item-code', // max 11 chars
            'texto' => '', // empty not allowed
        ];
        $this->session(['Auth' => $relator, 'selected_evento_id' => 2]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/items/add', $invalidData);
        $this->assertResponseOk();
        // The validator returns error messages
        $this->assertResponseContains('Item não pôde ser salvo:');
    }

    /**
     * Test edit method
     *
     * @return void
     */
    public function testEdit(): void
    {
        $relator = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);

        // 1. Relator can edit their own item
        $this->session(['Auth' => $relator, 'selected_evento_id' => 2]);
        $this->get('/items/edit/5'); // Item 5 owned by user 3
        $this->assertResponseOk();

        // Valid data: support 2 belongs to event 2, has numero_texto = 2.
        $data = [
            'apoio_id' => 2,
            'tr' => 2,
            'item' => '02.99',
            'texto' => 'Updated item 5 text',
        ];
        $this->session(['Auth' => $relator, 'selected_evento_id' => 2]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/items/edit/5', $data);
        $this->assertRedirect(['action' => 'index']);

        // Submit edit with mismatched TR (apoio 2 has numero_texto = 2, we submit tr = 3)
        $mismatchedTrData = [
            'apoio_id' => 2,
            'tr' => 3,
            'item' => '02.99',
            'texto' => 'Updated item 5 text',
        ];
        $this->session(['Auth' => $relator, 'selected_evento_id' => 2]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/items/edit/5', $mismatchedTrData);
        $this->assertResponseOk();
        $this->assertResponseContains('Apoios.numero_texto deve ser igual ao Item.tr.');

        // Submit edit with mismatched item prefix (apoio 2 has numero_texto = 2, we submit item prefix = 03)
        $mismatchedPrefixData = [
            'apoio_id' => 2,
            'tr' => 2,
            'item' => '03.99',
            'texto' => 'Updated item 5 text',
        ];
        $this->session(['Auth' => $relator, 'selected_evento_id' => 2]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/items/edit/5', $mismatchedPrefixData);
        $this->assertResponseOk();
        $this->assertResponseContains('Os dois primeiros dígitos do item devem ser iguais a Apoios.numero_texto (02).');

        // Submit edit with mismatched selected_evento_id (selected event 1, but support 2 belongs to event 2)
        $mismatchedEventData = [
            'apoio_id' => 2,
            'tr' => 2,
            'item' => '02.99',
            'texto' => 'Updated item 5 text',
        ];
        $this->session(['Auth' => $relator, 'selected_evento_id' => 1]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/items/edit/5', $mismatchedEventData);
        $this->assertResponseOk();
        $this->assertResponseContains('O apoio não pertence ao evento selecionado.');

        // 2. Relator cannot edit other user's item
        $this->session(['Auth' => $relator, 'selected_evento_id' => 2]);
        $this->get('/items/edit/2'); // Item 2 owned by user 1
        $this->assertResponseCode(403);
    }

    /**
     * Test delete method
     *
     * @return void
     */
    public function testDelete(): void
    {
        $relator = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);

        // 1. Relator cannot delete other user's item
        $this->session(['Auth' => $relator]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/items/delete/2'); // Item 2 owned by user 1
        $this->assertResponseCode(403);

        // 2. Relator can delete their own item
        $this->session(['Auth' => $relator]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/items/delete/5'); // Item 5 owned by user 3
        $this->assertRedirect(['action' => 'index']);
    }
}
