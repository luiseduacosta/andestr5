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
            'username' => 'grupo1'
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
            'username' => 'grupo1'
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
            'username' => 'grupo1'
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
            'resultado' => 'aprovada',
            'votacao' => '15/0/0',
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
            'username' => 'grupo1'
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
            'resultado' => 'aprovada',
            'votacao' => '15/0/0',
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
            'username' => 'grupo1'
        ]);

        // 1. Check dropdown filtering in Add action
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 2
        ]);
        $this->get('/votacoes/add');
        $this->assertResponseOk();
        
        // Relator (ID 3) should see Item 99 owned by themselves (ID 5), but NOT Item 99 owned by Admin (ID 4)
        $items = $this->viewVariable('items');
        if (is_object($items) && method_exists($items, 'toArray')) {
            $items = $items->toArray();
        }
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
            'resultado' => 'aprovada',
            'votacao' => '15/0/0',
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
            'username' => 'grupo1'
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

        // 3. Query TR 2 on Active Event 2 (relator grupo1 vê itens mas não votacoes de grupo2)
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 2
        ]);
        $this->get('/votacoes/report?trs=2');
        $this->assertResponseOk();
        $this->assertResponseContains('TR 2');
        $this->assertResponseContains('Item 2');
        $this->assertResponseNotContains('Texto modificado 2'); // filtered by grupo
    }

    /**
     * Test Fase 1 — votarTr: rejeição da TR inteira.
     *
     * @return void
     */
    public function testVotarTr(): void
    {
        $relator = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);

        // GET: Show TR items for voting
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 1
        ]);
        $this->get('/votacoes/votar-tr/1/1');
        $this->assertResponseOk();
        $this->assertResponseContains('Item 1');
        $this->assertResponseContains('Fase 1');

        // POST: Rejeitar TR → cria Votacao por item
        $data = [
            'resultado' => 'suprimida',
            'votacao' => '12/3/0',
            'observacoes' => 'TR rejeitada pelo grupo',
        ];
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/votacoes/votar-tr/1/1', $data);
        $this->assertRedirect(['action' => 'index']);

        // Verify records were created with tr_suprimida=1
        $votacoesTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Votacoes');
        $rejected = $votacoesTable->find()
            ->where(['tr' => 1, 'user_id' => 3, 'resultado' => 'suprimida'])
            ->all();
        $this->assertNotEmpty($rejected);
        foreach ($rejected as $v) {
            $this->assertEquals('suprimida', $v->resultado);
            $this->assertEquals('12/3/0', $v->votacao);
        }

        // POST: TR não rejeitada → nada registrado
        $beforeCount = $votacoesTable->find()->count();
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 2
        ]);
        $data2 = [
            'resultado' => 'aprovada',
            'votacao' => '15/0/0',
            'observacoes' => '',
        ];
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/votacoes/votar-tr/1/2', $data2);
        $this->assertRedirect(['action' => 'index']);
        $afterCount = $votacoesTable->find()->count();
        $this->assertEquals($beforeCount, $afterCount);
    }

    /**
     * Test Fase 2 — votarItem: votação individual de item em discussão.
     *
     * @return void
     */
    public function testVotarItem(): void
    {
        $relator = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);

        // GET: Show item voting form
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 2
        ]);
        $this->get('/votacoes/votar-item/3');
        $this->assertResponseOk();
        $this->assertResponseContains('Item 3');
        $this->assertResponseContains('Texto Item 3');

        // POST: Vote item with modification and destaque
        $data = [
            'resultado' => 'modificada',
            'votacao' => '9/6/0',
            'item_modificada' => 'Texto modificado pelo grupo',
            'destaque_minoria' => 1,
            'tr_aprovada' => 0,
            'observacoes' => 'Minoria destacada',
        ];
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/votacoes/votar-item/3', $data);
        $this->assertRedirect(['action' => 'index']);

        // Verify record
        $votacoesTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Votacoes');
        $itemVote = $votacoesTable->find()
            ->where(['item_id' => 3, 'evento_id' => 2])
            ->first();
        $this->assertNotEmpty($itemVote);
        $this->assertEquals('modificada', $itemVote->resultado);
        $this->assertEquals('9/6/0', $itemVote->votacao);
        $this->assertEquals('Texto modificado pelo grupo', $itemVote->item_modificada);
        $this->assertEquals(1, $itemVote->destaque_minoria);
    }

    /**
     * Test Fase 3 — votarRestantes: aprovação em bloco dos não discutidos.
     *
     * @return void
     */
    public function testVotarRestantes(): void
    {
        $relator = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);

        // GET: Show remaining items in TR 3 (only Item 3, which has no vote in evento 2)
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 2
        ]);
        $this->get('/votacoes/votar-restantes/1/3');
        $this->assertResponseOk();
        $this->assertResponseContains('Item 3');
        $this->assertResponseContains('Fase 3');

        // POST: Approve all remaining items
        $data = [
            'votacao' => '15/0/0',
            'observacoes' => 'Aprovados em bloco',
        ];
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/votacoes/votar-restantes/1/3', $data);
        $this->assertRedirect(['action' => 'index']);

        // Verify records
        $votacoesTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Votacoes');
        $aprovados = $votacoesTable->find()
            ->where(['tr' => 3, 'evento_id' => 2, 'resultado' => 'aprovada'])
            ->all();
        $this->assertNotEmpty($aprovados);
        foreach ($aprovados as $v) {
            $this->assertEquals('15/0/0', $v->votacao);
            $this->assertEquals(0, $v->tr_suprimida);
        }
    }

    /**
     * Test report with destaque_minoria display.
     *
     * @return void
     */
    public function testReportWithDestaque(): void
    {
        // First create a vote with destaque_minoria
        $votacoesTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Votacoes');
        $destaque = $votacoesTable->newEntity([
            'user_id' => 3,
            'evento_id' => 1,
            'grupo' => 1,
            'tr' => 1,
            'tr_suprimida' => 0,
            'tr_aprovada' => 0,
            'item_id' => 1,
            'item' => 'Item 1',
            'resultado' => 'modificada',
            'votacao' => '9/6/0',
            'item_modificada' => 'Proposta minoritária',
            'data' => new \Cake\I18n\DateTime(),
            'observacoes' => 'Destaque',
            'destaque_minoria' => true,
        ]);
        $votacoesTable->save($destaque);

        $relator = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 1
        ]);
        $this->get('/votacoes/report?trs=1');
        $this->assertResponseOk();
        $this->assertResponseContains('Destaque de Minoria');
    }

    private function ativarEvento(int $id): void
    {
        \Cake\ORM\TableRegistry::getTableLocator()->get('Eventos')
            ->updateAll(['ativo' => false], ['1 = 1']);
        \Cake\ORM\TableRegistry::getTableLocator()->get('Eventos')
            ->updateAll(['ativo' => true], ['id' => $id]);
    }
}
