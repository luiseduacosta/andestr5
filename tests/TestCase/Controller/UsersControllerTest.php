<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\UsersController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use App\Model\Entity\User;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\UsersController Test Case
 */
class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Votacoes',
        'app.Eventos',
    ];

    /**
     * Test index method - unauthenticated redirects to login
     */
    public function testIndexUnauthenticated(): void
    {
        $this->get('/users');
        $this->assertRedirectContains('/users/login');
    }

    /**
     * Test index method - unauthorized (relator) returns 403
     */
    public function testIndexUnauthorized(): void
    {
        $user = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);
        $this->session(['Auth' => $user]);

        $this->get('/users');
        $this->assertResponseCode(403);
    }

    /**
     * Test index method - authorized (admin) returns 200 and list
     */
    public function testIndexAuthorized(): void
    {
        $user = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        $this->session(['Auth' => $user]);

        $this->get('/users');
        $this->assertResponseOk();
        $this->assertResponseContains('admin');
        $this->assertResponseContains('grupo1');
    }

    /**
     * Test view method - viewing self is allowed
     */
    public function testViewSelf(): void
    {
        $user = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);
        $this->session(['Auth' => $user]);

        $this->get('/users/view/3');
        $this->assertResponseOk();
        $this->assertResponseContains('grupo1');
    }

    /**
     * Test view method - viewing others is forbidden for non-admin/editor
     */
    public function testViewOtherForbidden(): void
    {
        $user = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);
        $this->session(['Auth' => $user]);

        $this->get('/users/view/1');
        $this->assertResponseCode(403);
    }

    /**
     * Test view method - admin can view others
     */
    public function testViewAdminAuthorized(): void
    {
        $user = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        $this->session(['Auth' => $user]);

        $this->get('/users/view/3');
        $this->assertResponseOk();
        $this->assertResponseContains('grupo1');
    }

    /**
     * Test add method by admin
     */
    public function testAddAdmin(): void
    {
        $user = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $data = [
            'username' => 'newuser',
            'password' => 'secret123',
            'role' => 'relator'
        ];

        $this->post('/users/add', $data);
        $this->assertRedirect(['action' => 'index']);

        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $newUser = $usersTable->findByUsername('newuser')->first();
        $this->assertNotEmpty($newUser);
        $this->assertEquals('relator', $newUser->role);
    }

    /**
     * Test edit method - self role modification (privilege escalation attempt) is blocked
     */
    public function testEditSelfNoRoleEscalation(): void
    {
        $user = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $data = [
            'username' => 'grupo1_edited',
            'role' => 'admin'
        ];

        $this->post('/users/edit/3', $data);
        $this->assertRedirect(['action' => 'view', 3]);

        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $updatedUser = $usersTable->get(3);
        $this->assertEquals('grupo1_edited', $updatedUser->username);
        $this->assertEquals('relator', $updatedUser->role);
    }

    /**
     * Test edit method - admin can edit roles
     */
    public function testEditAdminCanChangeRole(): void
    {
        $user = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $data = [
            'username' => 'grupo1',
            'role' => 'editor'
        ];

        $this->post('/users/edit/3', $data);
        $this->assertRedirect(['action' => 'index']);

        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $updatedUser = $usersTable->get(3);
        $this->assertEquals('editor', $updatedUser->role);
    }

    /**
     * Test delete method - deleting self is forbidden
     */
    public function testDeleteSelfForbidden(): void
    {
        $user = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/users/delete/1');
        $this->assertResponseCode(403);
    }

    /**
     * Test delete method - deleting others is allowed for admin
     */
    public function testDeleteOther(): void
    {
        $user = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/users/delete/3');
        $this->assertRedirect(['action' => 'index']);

        $usersTable = TableRegistry::getTableLocator()->get('Users');
        $exists = $usersTable->find()->where(['id' => 3])->first();
        $this->assertNull($exists);
    }

    /**
     * Test impersonation flow
     */
    public function testImpersonateAndStopImpersonate(): void
    {
        $admin = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        $this->session(['Auth' => $admin]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/users/impersonate/3');
        $this->assertRedirect(['controller' => 'Eventos', 'action' => 'index']);
        $this->assertSession(1, 'impersonated_by');

        $this->get('/users/stopImpersonate');
        $this->assertRedirect(['action' => 'index']);
        $this->assertSession(null, 'impersonated_by');
    }
}
