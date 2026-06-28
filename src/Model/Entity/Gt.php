<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Gt Entity
 *
 * @property int $id
 * @property string $sigla
 * @property string|null $nome
 * @property string|null $outras
 */
class Gt extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and specify the individual fields that can be mass assigned
     * separately.
     *
     * @var list<string>
     */
    protected array $_accessible = [
        'sigla' => true,
        'nome' => true,
        'outras' => true,
    ];
}
