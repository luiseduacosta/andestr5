<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Apoio Entity
 *
 * @property int $id
 * @property string|null $nomedoevento
 * @property int $evento_id
 * @property string $caderno
 * @property int $numero_texto
 * @property string $tema
 * @property string|null $gt
 * @property string $titulo
 * @property string $autor
 * @property string $texto
 *
 * @property \App\Model\Entity\Evento $evento
 * @property \App\Model\Entity\Item[] $items
 */
class Apoio extends Entity
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
        'nomedoevento' => true,
        'evento_id' => true,
        'caderno' => true,
        'numero_texto' => true,
        'tema' => true,
        'gt' => true,
        'titulo' => true,
        'autor' => true,
        'texto' => true,
        'evento' => true,
        'items' => true,
    ];
}
