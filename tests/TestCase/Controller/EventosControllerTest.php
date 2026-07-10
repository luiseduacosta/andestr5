<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\EventosController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use App\Model\Entity\User;

/**
 * App\Controller\EventosController Test Case
 */
class EventosControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Eventos',
        'app.Apoios',
        'app.Votacoes',
        'app.Users',
    ];

    /**
     * Test select method with admin role
     *
     * @return void
     */
    public function testSelectAdmin(): void
    {
        $user = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        $this->session(['Auth' => $user]);

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $this->post('/eventos/select', ['evento_id' => 1]);
        $this->assertSession(1, 'selected_evento_id');
        $this->assertRedirect();
    }

    /**
     * Test select method with editor role
     *
     * @return void
     */
    public function testSelectEditor(): void
    {
        $user = new User([
            'id' => 2,
            'role' => 'editor',
            'username' => 'editor'
        ]);
        $this->session(['Auth' => $user]);

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $this->post('/eventos/select', ['evento_id' => 1]);
        $this->assertSession(1, 'selected_evento_id');
        $this->assertRedirect();
    }

    /**
     * Test select method with relator role (unauthorized)
     *
     * @return void
     */
    public function testSelectRelatorUnauthorized(): void
    {
        $user = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'relator'
        ]);
        $this->session([
            'Auth' => $user,
            'selected_evento_id' => 2 // default to 2
        ]);

        $this->enableCsrfToken();
        $this->enableSecurityToken();
        
        $this->post('/eventos/select', ['evento_id' => 1]);
        // The session should still have the old value (2)
        $this->assertSession(2, 'selected_evento_id');
        $this->assertRedirect();
    }

    /**
     * Test ativar method with inactive event
     *
     * @return void
     */
    public function testAtivarInactive(): void
    {
        $user = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        $this->session(['Auth' => $user]);

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/eventos/ativar/1');
        $this->assertRedirect();

        // Check if event 1 is active, and event 2 is inactive in the database
        $eventosTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Eventos');
        $evento1 = $eventosTable->get(1);
        $evento2 = $eventosTable->get(2);

        $this->assertTrue($evento1->ativo);
        $this->assertFalse($evento2->ativo);
    }

    /**
     * Test ativar method with already active event
     *
     * @return void
     */
    public function testAtivarAlreadyActive(): void
    {
        $user = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        $this->session(['Auth' => $user]);

        // First, set event 1 as active directly in DB
        $eventosTable = \Cake\ORM\TableRegistry::getTableLocator()->get('Eventos');
        $evento1 = $eventosTable->get(1);
        $evento1->ativo = true;
        $eventosTable->save($evento1);

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        // Call ativar on event 1 again
        $this->post('/eventos/ativar/1');
        $this->assertRedirect();

        // Check if event 1 remains active
        $evento1 = $eventosTable->get(1);
        $this->assertTrue($evento1->ativo, 'Event 1 should remain active when activated again');
    }

    /**
     * Test delete method clears selected_evento_id from session if the deleted event was selected
     *
     * @return void
     */
    public function testDeleteClearsSession(): void
    {
        $user = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        $this->session([
            'Auth' => $user,
            'selected_evento_id' => 1
        ]);

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/eventos/delete/1');
        $this->assertRedirect();

        // The session key selected_evento_id should have been cleared (null)
        $this->assertSession(null, 'selected_evento_id');
    }

    /**
     * Test delete method does not clear selected_evento_id from session if a different event is deleted
     *
     * @return void
     */
    public function testDeleteDoesNotClearOtherSession(): void
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

        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/eventos/delete/1');
        $this->assertRedirect();

        // The session key selected_evento_id should still be 2
        $this->assertSession(2, 'selected_evento_id');
    }
}

