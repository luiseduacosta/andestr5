<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Controller\GtsController;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use App\Model\Entity\User;
use Cake\ORM\TableRegistry;

/**
 * App\Controller\GtsController Test Case
 */
class GtsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * Fixtures
     *
     * @var list<string>
     */
    protected array $fixtures = [
        'app.Gts',
        'app.Apoios',
        'app.Eventos',
        'app.Users',
    ];

    /**
     * Test index method
     *
     * @return void
     */
    public function testIndex(): void
    {
        $user = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);
        $this->session(['Auth' => $user]);

        $this->get('/gts');
        $this->assertResponseOk();
        $this->assertResponseContains('GT1');
        $this->assertResponseContains('GT2');
    }

    /**
     * Test view method
     *
     * @return void
     */
    public function testView(): void
    {
        $user = new User([
            'id' => 3,
            'role' => 'relator',
            'username' => 'grupo1'
        ]);
        $this->session(['Auth' => $user]);

        $this->get('/gts/view/1');
        $this->assertResponseOk();
        $this->assertResponseContains('GT1');
        $this->assertResponseContains('Grupo de Trabalho 1');
    }

    /**
     * Test add method by admin (authorized)
     *
     * @return void
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
            'sigla' => 'GT3',
            'nome' => 'Grupo de Trabalho 3',
            'outras' => 'Outras 3'
        ];

        $this->post('/gts/add', $data);
        $this->assertRedirect(['action' => 'index']);

        $gtsTable = TableRegistry::getTableLocator()->get('Gts');
        $newGt = $gtsTable->findBySigla('GT3')->first();
        $this->assertNotEmpty($newGt);
        $this->assertEquals('Grupo de Trabalho 3', $newGt->nome);
    }

    /**
     * Test add method by editor (authorized)
     *
     * @return void
     */
    public function testAddEditor(): void
    {
        $user = new User([
            'id' => 2,
            'role' => 'editor',
            'username' => 'editor'
        ]);
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $data = [
            'sigla' => 'GT4',
            'nome' => 'Grupo de Trabalho 4',
            'outras' => 'Outras 4'
        ];

        $this->post('/gts/add', $data);
        $this->assertRedirect(['action' => 'index']);

        $gtsTable = TableRegistry::getTableLocator()->get('Gts');
        $newGt = $gtsTable->findBySigla('GT4')->first();
        $this->assertNotEmpty($newGt);
        $this->assertEquals('Grupo de Trabalho 4', $newGt->nome);
    }

    /**
     * Test add method by relator (unauthorized -> 403)
     *
     * @return void
     */
    public function testAddRelatorForbidden(): void
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
            'sigla' => 'GT5',
            'nome' => 'Grupo de Trabalho 5',
            'outras' => 'Outras 5'
        ];

        $this->post('/gts/add', $data);
        $this->assertResponseCode(403);
    }

    /**
     * Test edit method by admin (authorized)
     *
     * @return void
     */
    public function testEditAdmin(): void
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
            'sigla' => 'GT1-Edited',
            'nome' => 'Grupo de Trabalho 1 Editado'
        ];

        $this->post('/gts/edit/1', $data);
        $this->assertRedirect(['action' => 'index']);

        $gtsTable = TableRegistry::getTableLocator()->get('Gts');
        $gt = $gtsTable->get(1);
        $this->assertEquals('GT1-Edited', $gt->sigla);
        $this->assertEquals('Grupo de Trabalho 1 Editado', $gt->nome);
    }

    /**
     * Test edit method by editor (authorized)
     *
     * @return void
     */
    public function testEditEditor(): void
    {
        $user = new User([
            'id' => 2,
            'role' => 'editor',
            'username' => 'editor'
        ]);
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $data = [
            'sigla' => 'GT2-Edited',
            'nome' => 'Grupo de Trabalho 2 Editado'
        ];

        $this->post('/gts/edit/2', $data);
        $this->assertRedirect(['action' => 'index']);

        $gtsTable = TableRegistry::getTableLocator()->get('Gts');
        $gt = $gtsTable->get(2);
        $this->assertEquals('GT2-Edited', $gt->sigla);
        $this->assertEquals('Grupo de Trabalho 2 Editado', $gt->nome);
    }

    /**
     * Test edit method by relator (unauthorized -> 403)
     *
     * @return void
     */
    public function testEditRelatorForbidden(): void
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
            'sigla' => 'GT1-Forbidden',
            'nome' => 'Forbidden Edit'
        ];

        $this->post('/gts/edit/1', $data);
        $this->assertResponseCode(403);
    }

    /**
     * Test delete method by admin (authorized)
     *
     * @return void
     */
    public function testDeleteAdmin(): void
    {
        $user = new User([
            'id' => 1,
            'role' => 'admin',
            'username' => 'admin'
        ]);
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/gts/delete/1');
        $this->assertRedirect(['action' => 'index']);

        $gtsTable = TableRegistry::getTableLocator()->get('Gts');
        $exists = $gtsTable->find()->where(['id' => 1])->first();
        $this->assertNull($exists);
    }

    /**
     * Test delete method by editor (unauthorized -> 403)
     *
     * @return void
     */
    public function testDeleteEditorForbidden(): void
    {
        $user = new User([
            'id' => 2,
            'role' => 'editor',
            'username' => 'editor'
        ]);
        $this->session(['Auth' => $user]);
        $this->enableCsrfToken();
        $this->enableSecurityToken();

        $this->post('/gts/delete/1');
        $this->assertResponseCode(403);
    }
}
