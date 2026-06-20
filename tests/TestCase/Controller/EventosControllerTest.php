<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\EventosController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use App\Model\Entity\User;

/**
 * App\Controller\EventosController Test Case
 *
 * @uses \App\Controller\EventosController
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
}
