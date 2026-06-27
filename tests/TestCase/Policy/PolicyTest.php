<?php
declare(strict_types=1);

namespace App\Test\TestCase\Policy;

use App\Model\Entity\Apoio;
use App\Model\Entity\Evento;
use App\Model\Entity\Item;
use App\Model\Entity\User;
use App\Model\Entity\Votacao;
use App\Policy\ApoioPolicy;
use App\Policy\EventoPolicy;
use App\Policy\ItemPolicy;
use App\Policy\UserPolicy;
use App\Policy\VotacaoPolicy;
use Authorization\IdentityInterface;
use Cake\TestSuite\TestCase;

class PolicyTest extends TestCase
{
    /**
     * Helper to build a test identity wrapping a user entity.
     *
     * @param array $data
     * @return \Authorization\IdentityInterface
     */
    protected function getIdentity(array $data): IdentityInterface
    {
        $user = new User($data);
        $service = $this->createMock(\Authorization\AuthorizationServiceInterface::class);

        return new \Authorization\IdentityDecorator($service, $user);
    }

    /**
     * Test UserPolicy rules.
     */
    public function testUserPolicy(): void
    {
        $policy = new UserPolicy();
        $admin = $this->getIdentity(['id' => 1, 'role' => 'admin']);
        $editor = $this->getIdentity(['id' => 2, 'role' => 'editor']);
        $user1 = $this->getIdentity(['id' => 3, 'role' => 'user']);

        $targetUser = new User(['id' => 4, 'role' => 'user']);
        $selfUser = new User(['id' => 3, 'role' => 'user']);

        // Index/view
        $this->assertTrue($policy->canIndex($admin));
        $this->assertFalse($policy->canIndex($user1));
        $this->assertTrue($policy->canView($admin, $targetUser));
        $this->assertTrue($policy->canView($user1, $targetUser));

        // Add
        $this->assertTrue($policy->canAdd($admin, $targetUser));
        $this->assertTrue($policy->canAdd($editor, $targetUser));
        $this->assertFalse($policy->canAdd($user1, $targetUser));

        // Edit
        $this->assertTrue($policy->canEdit($admin, $targetUser));
        $this->assertTrue($policy->canEdit($editor, $targetUser));
        $this->assertTrue($policy->canEdit($user1, $selfUser));
        $this->assertFalse($policy->canEdit($user1, $targetUser));

        // Delete
        $this->assertTrue($policy->canDelete($admin, $targetUser));
        $this->assertTrue($policy->canDelete($editor, $targetUser));
        $this->assertFalse($policy->canDelete($user1, $targetUser));
    }

    /**
     * Test EventoPolicy rules.
     */
    public function testEventoPolicy(): void
    {
        $policy = new EventoPolicy();
        $admin = $this->getIdentity(['id' => 1, 'role' => 'admin']);
        $editor = $this->getIdentity(['id' => 2, 'role' => 'editor']);
        $user1 = $this->getIdentity(['id' => 3, 'role' => 'user']);
        $evento = new Evento();

        $this->assertTrue($policy->canIndex($admin));
        $this->assertTrue($policy->canIndex($user1));
        $this->assertTrue($policy->canView($admin, $evento));
        $this->assertTrue($policy->canView($user1, $evento));

        $this->assertTrue($policy->canAdd($admin, $evento));
        $this->assertTrue($policy->canAdd($editor, $evento));
        $this->assertFalse($policy->canAdd($user1, $evento));

        $this->assertTrue($policy->canEdit($admin, $evento));
        $this->assertTrue($policy->canEdit($editor, $evento));
        $this->assertFalse($policy->canEdit($user1, $evento));

        $this->assertTrue($policy->canDelete($admin, $evento));
        $this->assertTrue($policy->canDelete($editor, $evento));
        $this->assertFalse($policy->canDelete($user1, $evento));
    }

    /**
     * Test ApoioPolicy rules.
     */
    public function testApoioPolicy(): void
    {
        $policy = new ApoioPolicy();
        $admin = $this->getIdentity(['id' => 1, 'role' => 'admin']);
        $editor = $this->getIdentity(['id' => 2, 'role' => 'editor']);
        $user1 = $this->getIdentity(['id' => 3, 'role' => 'user']);
        $apoio = new Apoio();

        $this->assertTrue($policy->canIndex($admin));
        $this->assertTrue($policy->canIndex($user1));
        $this->assertTrue($policy->canView($admin, $apoio));
        $this->assertTrue($policy->canView($user1, $apoio));

        $this->assertTrue($policy->canAdd($admin, $apoio));
        $this->assertTrue($policy->canAdd($editor, $apoio));
        $this->assertFalse($policy->canAdd($user1, $apoio));

        $this->assertTrue($policy->canEdit($admin, $apoio));
        $this->assertTrue($policy->canEdit($editor, $apoio));
        $this->assertFalse($policy->canEdit($user1, $apoio));

        $this->assertTrue($policy->canDelete($admin, $apoio));
        $this->assertTrue($policy->canDelete($editor, $apoio));
        $this->assertFalse($policy->canDelete($user1, $apoio));
    }

    /**
     * Test ItemPolicy rules.
     */
    public function testItemPolicy(): void
    {
        $policy = new ItemPolicy();
        $admin = $this->getIdentity(['id' => 1, 'role' => 'admin']);
        $editor = $this->getIdentity(['id' => 2, 'role' => 'editor']);
        $relator = $this->getIdentity(['id' => 3, 'role' => 'relator']);
        $user1 = $this->getIdentity(['id' => 4, 'role' => 'user']);
        $item = new Item();
        $item->user_id = 3; // relator owns this item

        $this->assertTrue($policy->canIndex($admin));
        $this->assertTrue($policy->canIndex($user1));
        $this->assertTrue($policy->canView($admin, $item));
        $this->assertTrue($policy->canView($user1, $item));

        $this->assertTrue($policy->canAdd($admin, $item));
        $this->assertTrue($policy->canAdd($editor, $item));
        $this->assertTrue($policy->canAdd($relator, $item));
        $this->assertFalse($policy->canAdd($user1, $item));

        $this->assertTrue($policy->canEdit($admin, $item));
        $this->assertTrue($policy->canEdit($editor, $item));
        $this->assertTrue($policy->canEdit($relator, $item));
        $this->assertFalse($policy->canEdit($user1, $item));

        $this->assertTrue($policy->canDelete($admin, $item));
        $this->assertTrue($policy->canDelete($editor, $item));
        $this->assertTrue($policy->canDelete($relator, $item));
        $this->assertFalse($policy->canDelete($user1, $item));

        // .99 item tests
        $item99 = new Item(['item' => '99.99', 'user_id' => 3]);
        $otherRelator = $this->getIdentity(['id' => 5, 'role' => 'relator']);

        $this->assertTrue($policy->canView($relator, $item99));
        $this->assertFalse($policy->canView($otherRelator, $item99));

        $this->assertTrue($policy->canEdit($relator, $item99));
        $this->assertFalse($policy->canEdit($otherRelator, $item99));

        $this->assertTrue($policy->canDelete($relator, $item99));
        $this->assertFalse($policy->canDelete($otherRelator, $item99));
    }

    /**
     * Test VotacaoPolicy rules.
     */
    public function testVotacaoPolicy(): void
    {
        $policy = new VotacaoPolicy();
        $admin = $this->getIdentity(['id' => 1, 'role' => 'admin']);
        $editor = $this->getIdentity(['id' => 2, 'role' => 'editor']);
        $relator = $this->getIdentity(['id' => 3, 'role' => 'relator']);
        $otherRelator = $this->getIdentity(['id' => 4, 'role' => 'relator']);

        $relatorVotacao = new Votacao(['user_id' => 3]);

        $this->assertTrue($policy->canIndex($admin));
        $this->assertTrue($policy->canIndex($relator));

        // View
        $this->assertTrue($policy->canView($admin, $relatorVotacao));
        $this->assertTrue($policy->canView($editor, $relatorVotacao));
        $this->assertTrue($policy->canView($relator, $relatorVotacao));
        $this->assertFalse($policy->canView($otherRelator, $relatorVotacao));

        // Add
        $this->assertTrue($policy->canAdd($admin, $relatorVotacao));
        $this->assertTrue($policy->canAdd($relator, $relatorVotacao));
        $this->assertFalse($policy->canAdd($editor, $relatorVotacao));

        // Edit
        $this->assertTrue($policy->canEdit($admin, $relatorVotacao));
        $this->assertFalse($policy->canEdit($editor, $relatorVotacao));
        $this->assertTrue($policy->canEdit($relator, $relatorVotacao));
        $this->assertFalse($policy->canEdit($otherRelator, $relatorVotacao));

        // Delete
        $this->assertTrue($policy->canDelete($admin, $relatorVotacao));
        $this->assertFalse($policy->canDelete($editor, $relatorVotacao));
        $this->assertTrue($policy->canDelete($relator, $relatorVotacao));
        $this->assertFalse($policy->canDelete($otherRelator, $relatorVotacao));
    }
}
