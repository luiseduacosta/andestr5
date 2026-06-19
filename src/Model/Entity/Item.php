<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Item Entity
 *
 * @property int $id
 * @property int $apoio_id
 * @property int $tr
 * @property string $item
 * @property string $texto
 *
 * @property \App\Model\Entity\Apoio $apoio
 * @property \App\Model\Entity\Votacao[] $votacoes
 */
class Item extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'apoio_id' => true,
        'tr' => true,
        'item' => true,
        'texto' => true,
        'apoio' => true,
        'votacoes' => true,
    ];
}
