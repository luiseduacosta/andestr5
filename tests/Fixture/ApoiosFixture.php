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
            [
                'id' => 3,
                'nomedoevento' => 'Evento 3',
                'evento_id' => 3,
                'caderno' => 'Anexo',
                'numero_texto' => 3,
                'tema' => 'III',
                'gt' => 'GT 3',
                'gt_id' => 3,
                'titulo' => '&Atilde;&sup3;timo',
                'autor' => '<p>Contribui&ccedil;&atilde;o do(a)s professore(a)s Alexandre Freitas; Renato Fonseca; Carolina Em&Atilde;&shy;lia da Silva - Diretoria e Conselho de Representantes da ADOPEAD - SSind</p>',
                'texto' => 'Texto &Atilde;&ordm;nico',
            ],
            [
                'id' => 4,
                'nomedoevento' => 'Evento 4',
                'evento_id' => 4,
                'caderno' => 'Principal',
                'numero_texto' => 4,
                'tema' => 'IV',
                'gt' => 'GT 4',
                'gt_id' => 4,
                'titulo' => 'Ã‰ isso',
                'autor' => 'Autor 4',
                'texto' => 'â€œprogramas de financiamento estudantilâ€' . "\xc2\x9d" . ' como PROUNI e FIES. NÃƒO.',
            ],
        ];
        parent::init();
    }
}
