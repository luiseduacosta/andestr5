<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\User;
use Authorization\IdentityInterface;

class UserPolicy
{
    /**
     * Check if the user can index users.
     */
    public function canIndex(IdentityInterface $user): bool
    {
        return $user->role === 'admin' || $user->role === 'editor';
    }

    /**
     * Check if the user can view a user profile.
     */
    public function canView(IdentityInterface $user, User $resource): bool
    {
        return true;
    }

    /**
     * Check if the user can add a user.
     */
    public function canAdd(IdentityInterface $user, User $resource): bool
    {
        return $user->role === 'admin' || $user->role === 'editor';
    }

    /**
     * Check if the user can edit a user.
     */
    public function canEdit(IdentityInterface $user, User $resource): bool
    {
        return $user->role === 'admin' || $user->role === 'editor' || (int)$user->id === (int)$resource->id;
    }

    /**
     * Check if the user can delete a user.
     */
    public function canDelete(IdentityInterface $user, User $resource): bool
    {
        return $user->role === 'admin' || $user->role === 'editor';
    }

    /**
     * Check if the user can impersonate another user (admin only).
     */
    public function canImpersonate(IdentityInterface $user, User $resource): bool
    {
        return $user->role === 'admin';
    }
}
