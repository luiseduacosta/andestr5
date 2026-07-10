<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\VotacoesController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use App\Model\Entity\User;

/**
 * App\Controller\VotacoesController Test Case
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
     * Test index method with group filtering
     *
     * @return void
     */
    public function testIndexGroupFilter(): void
    {
        $admin = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);

        $this->session([
            'Auth' => $admin,
            'selected_evento_id' => 1
        ]);

        // Query with group filter = 1. Vote 1 is from grupo 1, so it should be visible.
        $this->get('/votacoes?grupo_filter=1');
        $this->assertResponseOk();
        $this->assertResponseContains('Item 1');

        // Query with group filter = 2. Vote 1 is from group 1, so it should NOT be visible.
        $this->get('/votacoes?grupo_filter=2');
        $this->assertResponseOk();
        $this->assertResponseNotContains('Item 1');
    }

    /**
     * Test view method
     *
     * @return void
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
            'item_id' => 4, // Unauthorized item
            'item' => '04.99',
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
        $this->get('/votacoes/relatorio');
        $this->assertResponseOk();
        $this->assertResponseContains('Relatório de Votações por TR');

        // 2. Query TR 1 on Active Event 1
        $this->get('/votacoes/relatorio?trs=1');
        $this->assertResponseOk();
        $this->assertResponseContains('TR 1');
        $this->assertResponseContains('Item 1');
        $this->assertResponseContains('Texto modificado 1'); // proposed modification

        // 3. Query TR 2 on Active Event 2 (relator grupo1 has no votes in evento 2 → no results)
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 2
        ]);
        $this->get('/votacoes/relatorio?trs=2');
        $this->assertResponseOk();
        // Relator grupo1 has no votes in evento 2, so TR 2 is not shown
        $this->assertResponseNotContains('TR 2');
    }

    /**
     * Test report action compiles items and votes correctly in Markdown download format.
     *
     * @return void
     */
    public function testReportDownload(): void
    {
        $relator = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);

        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 1
        ]);
        $this->get('/votacoes/relatorio?trs=1&download=markdown');
        $this->assertResponseOk();
        $this->assertHeader('Content-Disposition', 'attachment; filename="relatorio.md"');
        $this->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
        
        $this->assertResponseContains('# Evento 1');
        $this->assertResponseContains('- **Data:** 2026-06-19');
        $this->assertResponseContains('- **Local:** Local 1');
        $this->assertResponseContains('- **Relator:** grupo1');
        $this->assertResponseContains('- **Grupo:** G1');
        $this->assertResponseContains('## TR 1');
        $this->assertResponseContains('### Item Item 1');
        $this->assertResponseContains('| Grupo | Voto | Resultado | Relator |');
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
            'selected_evento_id' => 2
        ]);
        $this->get('/votacoes/votar-tr/1/3');
        $this->assertResponseOk();
        $this->assertResponseContains('Item 3');
        $this->assertResponseContains('Fase 1');

        // POST: Rejeitar TR → cria Votacao por item
        $data = [
            'resultado' => 'suprimida',
            'votacao' => '12/3/0',
            'observacoes' => 'TR rejeitada pelo grupo',
        ];
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/votacoes/votar-tr/1/3', $data);
        $this->assertRedirect(['action' => 'index']);

        // Verify records were created with resultado='suprimida'
        $votacoesTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Votacoes');
        $rejected = $votacoesTable->find()
            ->where(['tr' => 3, 'user_id' => 3, 'resultado' => 'suprimida'])
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
        $this->get('/votacoes/relatorio?trs=1');
        $this->assertResponseOk();
        $this->assertResponseContains('Destaque de Minoria');
    }

    /**
     * Test votarItem authorization constraints.
     *
     * @return void
     */
    public function testVotarItemAuthorization(): void
    {
        // 1. Non-relator user (admin) visits votar-item -> access denied (redirects to index)
        $admin = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        $this->session([
            'Auth' => $admin,
            'selected_evento_id' => 2
        ]);
        $this->get('/votacoes/votar-item/3');
        $this->assertRedirect(['action' => 'index']);

        // 2. Relator tries to vote on another relator's *.99 item (ForbiddenException)
        $relator = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 2
        ]);
        
        // Item 4 is '04.99' owned by User 1 (Admin). Relator (User 3) should be forbidden.
        $this->get('/votacoes/votar-item/4');
        $this->assertResponseCode(403);
    }

    /**
     * Test inserirItem successfully creates a new item and vote without validation failure.
     *
     * @return void
     */
    public function testInserirItemSuccess(): void
    {
        $relator = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);
        // Evento 2, Apoio 2 exists (so we can vote on TR 2)
        $this->session([
            'Auth' => $relator,
            'selected_evento_id' => 2
        ]);

        $data = [
            'texto' => 'Novo item de inclusao test',
            'votacao' => '10/5/0',
            'destaque_minoria' => 0,
            'observacoes' => 'Inserido com sucesso',
        ];
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        // POST to inserir-item/1/2 (grupo 1, TR 2)
        $this->post('/votacoes/inserir-item/1/2', $data);
        $this->assertRedirect(['action' => 'index']);

        // Check if the item and vote were created
        $itemsTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Items');
        $newItem = $itemsTable->find()->where(['texto' => 'Novo item de inclusao test'])->first();
        $this->assertNotEmpty($newItem);
        $this->assertEquals('02.99', $newItem->item);

        $votacoesTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Votacoes');
        $newVote = $votacoesTable->find()->where(['item_id' => $newItem->id])->first();
        $this->assertNotEmpty($newVote);
        $this->assertEquals('inclusão', $newVote->resultado);
        $this->assertEquals('10/5/0', $newVote->votacao);
    }

    /**
     * Test findItensSemVoto filters correctly on group level.
     *
     * @return void
     */
    public function testFindItensSemVotoGroupBased(): void
    {
        $votacoesTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Votacoes');
        
        // Create another user in the same group (grupo 1) but with different user_id
        $relator1 = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);
        
        // Log in as relator1 and vote on Item 3 in Evento 2
        $vote = $votacoesTable->newEntity([
            'user_id' => 3,
            'evento_id' => 2,
            'grupo' => 1,
            'tr' => 3,
            'item_id' => 3,
            'item' => 'Item 3',
            'resultado' => 'aprovada',
            'votacao' => '15/0/0',
            'item_modificada' => '',
            'data' => new \Cake\I18n\DateTime(),
            'destaque_minoria' => false,
        ]);
        $votacoesTable->save($vote);

        // Now find remaining items for another user in the same group (grupo 1)
        // Even though this user has id = 99, they belong to grupo1 (username = grupo1).
        $options = [
            'grupo' => 1,
            'tr' => 3,
            'evento_id' => 2,
            'user_id' => 99,
        ];
        
        $query = $votacoesTable->findItensSemVoto($votacoesTable->Items->find(), $options);
        $itensRestantes = $query->all();
        
        // Item 3 is already voted by group 1, so it should NOT be in remaining items.
        // TR 3 has Item 3. Since Item 3 has a vote from group 1, remaining items should be empty.
        $this->assertTrue($itensRestantes->isEmpty(), 'Item 3 should be filtered out because group 1 already voted on it.');
    }

    /**
     * Test add fails gracefully when applyInclusionItem fails (e.g. invalid TR/support)
     * and shows a detailed validation error message on invalid votacao format.
     *
     * @return void
     */
    public function testAddGracefulFailureAndDetailedValidation(): void
    {
        $user = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);
        $this->session([
            'Auth' => $user,
            'selected_evento_id' => 2
        ]);

        // 1. applyInclusionItem fails (e.g., TR 999 does not exist)
        // This should not crash with undefined variables in add.php view
        $dataInvalidTr = [
            'grupo' => 1,
            'tr' => 999, // Non-existent TR support
            'resultado' => 'inclusao',
            'votacao' => '15/0/0',
            'item_modificada' => 'Novo item',
        ];
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/votacoes/add', $dataInvalidTr);
        $this->assertResponseOk(); // Renders the form again (no redirect, no crash)

        // 2. Votacao formatting validation failure displays detailed message
        $dataInvalidVote = [
            'grupo' => 1,
            'tr' => 2,
            'item_id' => 2,
            'item' => 'Item 2',
            'resultado' => 'aprovada',
            'votacao' => 'invalid-format', // invalid format
            'item_modificada' => '',
        ];
        $this->session([
            'Auth' => $user,
            'selected_evento_id' => 2
        ]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/votacoes/add', $dataInvalidVote);
        $this->assertResponseOk(); // Form re-renders
        $this->assertResponseContains('O campo votação deve estar no formato XX/XX/XX (ex: 15/6/0).');
    }

    private function ativarEvento(int $id): void
    {
        \Cake\ORM\TableRegistry::getTableLocator()->get('Eventos')
            ->updateAll(['ativo' => false], ['1 = 1']);
        \Cake\ORM\TableRegistry::getTableLocator()->get('Eventos')
            ->updateAll(['ativo' => true], ['id' => $id]);
    }
}
