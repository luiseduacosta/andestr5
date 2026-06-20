<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ApoiosFixture
 */
class ApoiosFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'nomedoevento' => 'Evento 1',
                'evento_id' => 1,
                'caderno' => 'Principal',
                'numero_texto' => 1,
                'tema' => 'I',
                'gt' => 'GT 1',
                'gt_id' => 1,
                'titulo' => 'Apoio Evento 1',
                'autor' => 'Autor 1',
                'texto' => 'Texto 1',
            ],
            [
                'id' => 2,
                'nomedoevento' => 'Evento 2',
                'evento_id' => 2,
                'caderno' => 'Anexo',
                'numero_texto' => 2,
                'tema' => 'II',
                'gt' => 'GT 2',
                'gt_id' => 2,
                'titulo' => 'Apoio Evento 2',
                'autor' => 'Autor 2',
                'texto' => 'Texto 2',
            ],
        ];
        parent::init();
    }
}
