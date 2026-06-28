<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Gt;
use Authorization\IdentityInterface;

class GtPolicy
{
    /**
     * Check if $user can create Gt
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Gt $gt
     * @return bool
     */
    public function canCreate(IdentityInterface $user): bool
    {
        return $user->role === 'admin' || $user->role === 'editor';
    }

    /**
     * Check if $user can update $gt
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Gt $gt
     * @return bool
     */
    public function canUpdate(IdentityInterface $user, Gt $gt): bool
    {
        return $user->role === 'admin' || $user->role === 'editor';
    }

    /**
     * Check if $user can delete $gt
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Gt $gt
     * @return bool
     */
    public function canDelete(IdentityInterface $user, Gt $gt): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Check if $user can view $gt
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @param \App\Model\Entity\Gt $gt
     * @return bool
     */
    public function canView(IdentityInterface $user, Gt $gt): bool
    {
        return true;
    }

    /**
     * Check if $user can index Gt
     *
     * @param \Authorization\IdentityInterface $user The user.
     * @return bool
     */
    public function canIndex(IdentityInterface $user): bool
    {
        return true;
    }
}
