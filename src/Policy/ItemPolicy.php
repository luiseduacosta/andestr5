<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Item;
use Authorization\IdentityInterface;

class ItemPolicy
{
    /**
     * Check if the user can index items.
     */
    public function canIndex(IdentityInterface $user): bool
    {
        return true;
    }

    /**
     * Check if the user can view an item.
     */
    public function canView(IdentityInterface $user, Item $resource): bool
    {
        return true;
    }

    /**
     * Check if the user can add an item.
     */
    public function canAdd(IdentityInterface $user, Item $resource): bool
    {
        return $user->role === 'admin' || $user->role === 'editor' || $user->role === 'relator';
    }

    /**
     * Check if the user can edit an item.
     */
    public function canEdit(IdentityInterface $user, Item $resource): bool
    {
        if ($user->role === 'admin' || $user->role === 'editor') {
            return true;
        }

        // Relator só edita itens que ele próprio criou
        if ($user->role === 'relator') {
            return (int)$resource->user_id === (int)$user->id;
        }

        return false;
    }

    /**
     * Check if the user can delete an item.
     */
    public function canDelete(IdentityInterface $user, Item $resource): bool
    {
        if ($user->role === 'admin' || $user->role === 'editor') {
            return true;
        }

        // Relator só deleta itens que ele próprio criou
        if ($user->role === 'relator') {
            return (int)$resource->user_id === (int)$user->id;
        }

        return false;
    }
}
