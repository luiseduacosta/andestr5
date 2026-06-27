<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Evento;
use Authorization\IdentityInterface;

class EventoPolicy
{
    /**
     * Check if the user can index events.
     */
    public function canIndex(IdentityInterface $user): bool
    {
        return true;
    }

    /**
     * Check if the user can view an event.
     */
    public function canView(IdentityInterface $user, Evento $resource): bool
    {
        return true;
    }

    /**
     * Check if the user can add an event.
     */
    public function canAdd(IdentityInterface $user, Evento $resource): bool
    {
        return $user->role === 'admin' || $user->role === 'editor';
    }

    /**
     * Check if the user can edit an event.
     */
    public function canEdit(IdentityInterface $user, Evento $resource): bool
    {
        return $user->role === 'admin' || $user->role === 'editor';
    }

    /**
     * Check if the user can delete an event.
     */
    public function canDelete(IdentityInterface $user, Evento $resource): bool
    {
        return $user->role === 'admin' || $user->role === 'editor';
    }

    public function canAtivar(IdentityInterface $user, Evento $resource): bool
    {
        return $user->role === 'admin' || $user->role === 'editor';
    }
}
