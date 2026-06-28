<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\FixEncodingCommand;
use Cake\Console\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\TestCase;
use Cake\ORM\TableRegistry;

/**
 * App\Command\FixEncodingCommand Test Case
 *
 * @link \App\Command\FixEncodingCommand
 */
class FixEncodingCommandTest extends TestCase
{
    use ConsoleIntegrationTestTrait;

    protected array $fixtures = [
        'app.Apoios',
    ];

    /**
     * Test execute method
     *
     * @return void
     * @link \App\Command\FixEncodingCommand::execute()
     */
    public function testExecute(): void
    {
        $table = TableRegistry::getTableLocator()->get('Apoios');

        // Verify initial state
        $rowBefore = $table->get(3);
        $this->assertStringContainsString('Em&Atilde;&shy;lia', $rowBefore->autor);
        $this->assertStringContainsString('&Atilde;&sup3;timo', $rowBefore->titulo);

        // Execute command
        $this->exec('fix_encoding');

        $this->assertExitCode(0);

        // Fetch row 3 after fix
        $rowAfter = $table->get(3);
        $this->assertEquals(
            '<p>Contribuição do(a)s professore(a)s Alexandre Freitas; Renato Fonseca; Carolina Emília da Silva - Diretoria e Conselho de Representantes da ADOPEAD - SSind</p>',
            $rowAfter->autor
        );
        $this->assertEquals('ótimo', $rowAfter->titulo);
        $this->assertEquals('Texto único', $rowAfter->texto);

        // Fetch row 4 after fix
        $row4After = $table->get(4);
        $this->assertEquals('É isso', $row4After->titulo);
        $this->assertEquals('“programas de financiamento estudantil” como PROUNI e FIES. NÃO.', $row4After->texto);
    }
}
