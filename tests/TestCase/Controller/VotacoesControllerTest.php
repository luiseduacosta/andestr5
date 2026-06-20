<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\VotacoesController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use App\Model\Entity\User;

/**
 * App\Controller\VotacoesController Test Case
 *
 * @uses \App\Controller\VotacoesController
 */
class VotacoesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Votacoes',
        'app.Users',
        'app.Eventos',
        'app.Items',
        'app.Apoios',
    ];

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\VotacoesController::index()
     */
    public function testIndex(): void
    {
        // 1. Relator user tests
        $relator = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'relator'
        ]);
        
        // Active event 2 by default. Relator has no vote records in Event 2.
        $this->session(['Auth' => $relator]);
        $this->get('/votacoes');
        $this->assertResponseOk();
        $this->assertResponseNotContains('Item 2'); // filtered out because it is from User 1 (Admin)
        $this->assertResponseNotContains('Item 1');

        // Active event 1. Relator has vote record 1 in Event 1.
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 1
        ]);
        $this->get('/votacoes');
        $this->assertResponseOk();
        $this->assertResponseContains('Item 1'); // visible because it is from User 3 (relator)
        $this->assertResponseNotContains('Item 2');

        // 2. Admin user tests
        $admin = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        
        // Active event 2. Admin should see the vote record on Item 2 (created by admin, user 1).
        $this->session([
            'Auth' => $admin,
            'selected_evento_id' => 2
        ]);
        $this->get('/votacoes');
        $this->assertResponseOk();
        $this->assertResponseContains('Item 2');
        $this->assertResponseNotContains('Item 1');

        // Active event 1. Admin should see all records in Event 1 (including Item 1 created by user 3).
        $this->session([
            'Auth' => $admin,
            'selected_evento_id' => 1
        ]);
        $this->get('/votacoes');
        $this->assertResponseOk();
        $this->assertResponseContains('Item 1');
        $this->assertResponseNotContains('Item 2');
    }

    /**
     * Test view method
     *
     * @return void
     * @uses \App\Controller\VotacoesController::view()
     */
    public function testView(): void
    {
        // View requires authorization, so let's log in as a relator (user 3).
        $user = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'relator'
        ]);
        $this->session(['Auth' => $user]);

        $this->get('/votacoes/view/1');
        $this->assertResponseOk();
        $this->assertResponseContains('Item 1');
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\VotacoesController::add()
     */
    public function testAdd(): void
    {
        // Relator can add a Votacao (Policy allows it).
        $user = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'relator'
        ]);
        $this->session([
            'Auth' => $user,
            'selected_evento_id' => 2 // active event
        ]);

        $this->get('/votacoes/add');
        $this->assertResponseOk();
        
        // Post data to add a Votacao.
        // It should automatically associate with active event 2.
        $data = [
            'user_id' => 3,
            'grupo' => 2,
            'tr' => 2,
            'tr_suprimida' => 0,
            'tr_aprovada' => 1,
            'item_id' => 2,
            'item' => 'Item New',
            'resultado' => 'Aprovado',
            'votacao' => 'Sim',
            'item_modificada' => 'Texto modificado',
            'observacoes' => 'Sem obs',
        ];
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/votacoes/add', $data);
        $this->assertRedirect(['action' => 'index']);

        // Check if the record was saved with event_id = 2.
        $votacoesTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Votacoes');
        $newVote = $votacoesTable->find()->order(['id' => 'DESC'])->first();
        $this->assertNotEmpty($newVote);
        $this->assertEquals(2, $newVote->evento_id);
    }

    /**
     * Test edit method
     *
     * @return void
     * @uses \App\Controller\VotacoesController::edit()
     */
    public function testEdit(): void
    {
        $user = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'relator'
        ]);
        $this->session([
            'Auth' => $user,
            'selected_evento_id' => 1 // active event is 1
        ]);

        $this->get('/votacoes/edit/1');
        $this->assertResponseOk();

        // Edit should force event_id to 1 (active event).
        $data = [
            'item' => 'Item Edit',
            'user_id' => 3,
            'grupo' => 1,
            'tr' => 1,
            'tr_suprimida' => 0,
            'tr_aprovada' => 1,
            'item_id' => 1,
            'resultado' => 'Aprovado',
            'votacao' => 'Sim',
            'item_modificada' => 'Texto modificado',
            'observacoes' => 'Sem obs',
        ];
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/votacoes/edit/1', $data);
        $this->assertRedirect(['action' => 'index']);

        $votacoesTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Votacoes');
        $vote = $votacoesTable->get(1);
        $this->assertEquals(1, $vote->evento_id);
    }

    /**
     * Test that relator users only see permitted items in the dropdown list
     * and are forbidden from voting on unauthorized `.99` items.
     *
     * @return void
     */
    public function testRelatorItemDropdownFilteringAndForbiddenAccess(): void
    {
        $relator = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'relator'
        ]);

        // 1. Check dropdown filtering in Add action
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 2
        ]);
        $this->get('/votacoes/add');
        $this->assertResponseOk();
        
        // Relator (ID 3) should see Item 99 owned by themselves (ID 5), but NOT Item 99 owned by Admin (ID 4)
        $items = $this->viewVariable('items')->toArray();
        $this->assertArrayHasKey(5, $items);
        $this->assertArrayNotHasKey(4, $items);

        // 2. Access via GET with unauthorized item_id should trigger ForbiddenException (403 status code)
        $this->get('/votacoes/add?item_id=4');
        $this->assertResponseCode(403);

        // 3. POST with unauthorized item_id should trigger ForbiddenException (403 status code)
        $data = [
            'grupo' => 2,
            'tr' => 2,
            'tr_suprimida' => 0,
            'tr_aprovada' => 1,
            'item_id' => 4, // Unauthorized item
            'item' => 'Item 99',
            'resultado' => 'Aprovado',
            'votacao' => 'Sim',
            'item_modificada' => '',
            'observacoes' => '',
        ];
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/votacoes/add', $data);
        $this->assertResponseCode(403);
    }

    /**
     * Test report action compiles items and votes correctly.
     *
     * @return void
     */
    public function testReportAction(): void
    {
        $relator = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'relator'
        ]);

        // 1. Unselected TRs page load
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 1
        ]);
        $this->get('/votacoes/report');
        $this->assertResponseOk();
        $this->assertResponseContains('Nenhuma TR selecionada');

        // 2. Query TR 1 on Active Event 1
        $this->get('/votacoes/report?trs=1');
        $this->assertResponseOk();
        $this->assertResponseContains('TR 1');
        $this->assertResponseContains('Item 1');
        $this->assertResponseContains('Texto modificado 1'); // proposed modification

        // 3. Query TR 2 on Active Event 2
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 2
        ]);
        $this->get('/votacoes/report?trs=2');
        $this->assertResponseOk();
        $this->assertResponseContains('TR 2');
        $this->assertResponseContains('Item 2');
        $this->assertResponseContains('Texto modificado 2');
    }
}
