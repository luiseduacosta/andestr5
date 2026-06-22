<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Votacao;
use Authorization\IdentityInterface;

class VotacaoPolicy
{
    /**
     * Check if the user can index votacoes.
     */
    public function canIndex(IdentityInterface $user): bool
    {
        return true;
    }

    /**
     * Check if the user can view reports.
     */
    public function canReport(IdentityInterface $user): bool
    {
        return true;
    }

    /**
     * Check if the user can view a votacao.
     */
    public function canView(IdentityInterface $user, Votacao $resource): bool
    {
        return $user->role === 'admin' || $user->role === 'editor' || (int)$user->id === (int)$resource->user_id;
    }

    /**
     * Check if the user can add a votacao.
     */
    public function canAdd(IdentityInterface $user, Votacao $resource): bool
    {
        return $user->role === 'admin' || $user->role === 'relator';
    }

    /**
     * Check if the user can edit a votacao.
     */
    public function canEdit(IdentityInterface $user, Votacao $resource): bool
    {
        return $user->role === 'admin' || ($user->role === 'relator' && (int)$user->id === (int)$resource->user_id);
    }

    /**
     * Check if the user can delete a votacao.
     */
    public function canDelete(IdentityInterface $user, Votacao $resource): bool
    {
        return $user->role === 'admin' || ($user->role === 'relator' && (int)$user->id === (int)$resource->user_id);
    }

    public function canVotarTr(IdentityInterface $user): bool
    {
        return $user->role === 'admin' || $user->role === 'relator';
    }

    public function canVotarItem(IdentityInterface $user): bool
    {
        return $user->role === 'admin' || $user->role === 'relator';
    }

    public function canVotarRestantes(IdentityInterface $user): bool
    {
        return $user->role === 'admin' || $user->role === 'relator';
    }
}
