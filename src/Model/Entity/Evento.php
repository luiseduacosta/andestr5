<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Evento Entity
 *
 * @property int $id
 * @property int $ordem
 * @property string $nome
 * @property string $data
 * @property string $local
 * @property bool $ativo
 *
 * @property \App\Model\Entity\Apoio[] $apoios
 * @property \App\Model\Entity\Votacao[] $votacoes
 */
class Evento extends Entity
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
        'ordem' => true,
        'nome' => true,
        'data' => true,
        'local' => true,
        'ativo' => true,
        'apoios' => true,
        'votacoes' => true,
    ];
}
