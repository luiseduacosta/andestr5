<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Apoio;
use Authorization\IdentityInterface;

class ApoioPolicy
{
    /**
     * Check if the user can index apoios.
     */
    public function canIndex(IdentityInterface $user): bool
    {
        return true;
    }

    /**
     * Check if the user can view an apoio.
     */
    public function canView(IdentityInterface $user, Apoio $resource): bool
    {
        return true;
    }

    /**
     * Check if the user can add an apoio.
     */
    public function canAdd(IdentityInterface $user, Apoio $resource): bool
    {
        return $user->role === 'admin' || $user->role === 'editor';
    }

    /**
     * Check if the user can edit an apoio.
     */
    public function canEdit(IdentityInterface $user, Apoio $resource): bool
    {
        return $user->role === 'admin' || $user->role === 'editor';
    }

    /**
     * Check if the user can delete an apoio.
     */
    public function canDelete(IdentityInterface $user, Apoio $resource): bool
    {
        return $user->role === 'admin' || $user->role === 'editor';
    }
}
