<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\ApoiosController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use App\Model\Entity\User;

/**
 * App\Controller\ApoiosController Test Case
 *
 * @uses \App\Controller\ApoiosController
 */
class ApoiosControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Apoios',
        'app.Eventos',
        'app.Users',
    ];

    /**
     * Test index method
     *
     * @return void
     * @uses \App\Controller\ApoiosController::index()
     */
    public function testIndex(): void
    {
        $user = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        $this->session(['Auth' => $user]);

        // Default event is last event: event 2.
        // Index should contain Apoio Evento 2 and not contain Apoio Evento 1.
        $this->get('/apoios');
        $this->assertResponseOk();
        $this->assertResponseContains('Apoio Evento 2');
        $this->assertResponseNotContains('Apoio Evento 1');

        // Set selected event to 1 in session.
        $this->session([
            'Auth' => $user,
            'selected_evento_id' => 1
        ]);
        $this->get('/apoios');
        $this->assertResponseOk();
        $this->assertResponseContains('Apoio Evento 1');
        $this->assertResponseNotContains('Apoio Evento 2');
    }

    /**
     * Test add method
     *
     * @return void
     * @uses \App\Controller\ApoiosController::add()
     */
    public function testAdd(): void
    {
        $user = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        $this->session([
            'Auth' => $user,
            'selected_evento_id' => 2
        ]);

        $this->get('/apoios/add');
        $this->assertResponseOk();

        $data = [
            'nomedoevento' => 'Evento 2',
            'caderno' => 'Principal',
            'numero_texto' => 3,
            'tema' => 'I',
            'gt' => 'GT 2',
            'gt_id' => 2,
            'titulo' => 'Novo Apoio',
            'autor' => 'Novo Autor',
            'texto' => 'Novo Texto',
        ];
        $this->enableCsrfToken();
        $this->enableSecurityToken();
        $this->post('/apoios/add', $data);
        $this->assertRedirect(['action' => 'index']);

        // Check it saved with event_id = 2.
        $apoiosTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Apoios');
        $newApoio = $apoiosTable->find()->order(['id' => 'DESC'])->first();
        $this->assertNotEmpty($newApoio);
        $this->assertEquals(2, $newApoio->evento_id);
    }
}
