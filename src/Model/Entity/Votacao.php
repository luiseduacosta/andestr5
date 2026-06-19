<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Votacao Entity
 *
 * @property int $id
 * @property int $user_id
 * @property int $evento_id
 * @property int $grupo
 * @property int $tr
 * @property int $tr_suprimida
 * @property int $tr_aprovada
 * @property int $item_id
 * @property string $resultado
 * @property string $votacao
 * @property string $item_modificada
 * @property \Cake\I18n\DateTime $data
 * @property string $observacoes
 *
 * @property \App\Model\Entity\Item $item
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Evento $evento
 */
class Votacao extends Entity
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
        'user_id' => true,
        'evento_id' => true,
        'grupo' => true,
        'tr' => true,
        'tr_suprimida' => true,
        'tr_aprovada' => true,
        'item_id' => true,
        'item' => true,
        'resultado' => true,
        'votacao' => true,
        'item_modificada' => true,
        'data' => true,
        'observacoes' => true,
        'user' => true,
        'evento' => true,
    ];
}
