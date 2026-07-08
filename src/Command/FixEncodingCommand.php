<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Console\Arguments;
use Cake\Command\Command;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\TableRegistry;

class FixEncodingCommand extends Command
{
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Fix mojibake encoding issues in apoios table')
            ->addOption('column', [
                'help' => 'Specific column to fix (autor, titulo, texto, or "all")',
                'short' => 'c',
                'default' => 'all',
            ])
            ->addOption('dry-run', [
                'help' => 'Show what would be fixed without saving',
                'short' => 'd',
                'boolean' => true,
            ]);

        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io)
    {
        $table = TableRegistry::getTableLocator()->get('Apoios');
        $columnsToFix = $args->getOption('column');
        $dryRun = $args->getOption('dry-run');

        if ($columnsToFix === 'all') {
            $columns = ['autor', 'titulo', 'texto'];
        } else {
            $columns = [(string)$columnsToFix];
        }

        $io->info('Starting encoding fix...');
        if ($dryRun) {
            $io->warning('DRY RUN - No changes will be saved');
        }

        // Get total count for progress
        $totalCount = $table->find()->count();
        $io->info("Total records: {$totalCount}");

        $fixedCount = 0;
        $skippedCount = 0;
        $errorCount = 0;

        // Process in batches to avoid memory issues
        $batchSize = 100;
        $offset = 0;

        while ($offset < $totalCount) {
            $rows = $table->find()
                ->limit($batchSize)
                ->offset($offset)
                ->all();

            foreach ($rows as $row) {
                $rowChanged = false;

                foreach ($columns as $column) {
                    if (!$row->has($column)) {
                        continue;
                    }

                    $content = $row->get($column);
                    if (empty($content)) {
                        continue;
                    }

                    // Check if content has mojibake
                    if (!$this->hasMojibake($content)) {
                        continue;
                    }

                    // Fix encoding
                    $original = $content;
                    $fixed = $this->fixEncoding($content);

                    if ($fixed !== $original) {
                        if (!$dryRun) {
                            $row->set($column, $fixed);
                        }
                        $rowChanged = true;

                        if ($offset < 10) { // Show first 10 records as sample
                            $io->out("  ID {$row->id} [{$column}]: " . substr($original, 0, 50) . '... → ' . substr($fixed, 0, 50) . '...');
                        }
                    }
                }

                if ($rowChanged) {
                    if (!$dryRun) {
                        // Use transaction for safety
                        $table->getConnection()->transactional(function () use ($table, $row) {
                            return $table->save($row);
                        });
                    }
                    $fixedCount++;
                } else {
                    $skippedCount++;
                }
            }

            $offset += $batchSize;
            $io->out("Progress: {$offset}/{$totalCount}");
        }

        $io->success('Encoding fix completed!');
        $io->info("Fixed: {$fixedCount}, Skipped: {$skippedCount}, Errors: {$errorCount}");
    }

    /**
     * Check if text contains mojibake patterns or HTML entities
     */
    private function hasMojibake(string $text): bool
    {
        // Check for HTML entities (named or numeric, e.g. &ccedil;, &atilde;, &#237;)
        if (preg_match('/&[a-zA-Z0-9#]+;/', $text)) {
            return true;
        }
        
        // Ensure string is valid UTF-8
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');

        // Check for CP1252 / UTF-8 stored as LATIN-1 patterns (double-encoded UTF-8)
        $pattern = '/[\x{00C2}-\x{00DF}][\x{0080}-\x{00BF}€‚ƒ„…†‡ˆ‰Š‹ŒŽ‘’“”•–—˜™š›œžŸ]|[\x{00E0}-\x{00EF}][\x{0080}-\x{00BF}€‚ƒ„…†‡ˆ‰Š‹ŒŽ‘’“”•–—˜™š›œžŸ]{2}|[\x{00F0}-\x{00F4}][\x{0080}-\x{00BF}€‚ƒ„…†‡ˆ‰Š‹ŒŽ‘’“”•–—˜™š›œžŸ]{3}/u';
        if (preg_match($pattern, $text)) {
            return true;
        }
        
        return false;
    }

    /**
     * Fix mojibake by selectively converting double-encoded segments
     */
    private function fixEncoding(string $text): string
    {
        // First, decode HTML entities to actual characters
        $decoded = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        // Ensure string is valid UTF-8 to prevent preg_replace_callback with /u from failing
        $decoded = mb_convert_encoding($decoded, 'UTF-8', 'UTF-8');

        // Target double-encoded UTF-8 sequences (using CP1252 and /u flag)
        $pattern = '/[\x{00C2}-\x{00DF}][\x{0080}-\x{00BF}€‚ƒ„…†‡ˆ‰Š‹ŒŽ‘’“”•–—˜™š›œžŸ]|[\x{00E0}-\x{00EF}][\x{0080}-\x{00BF}€‚ƒ„…†‡ˆ‰Š‹ŒŽ‘’“”•–—˜™š›œžŸ]{2}|[\x{00F0}-\x{00F4}][\x{0080}-\x{00BF}€‚ƒ„…†‡ˆ‰Š‹ŒŽ‘’“”•–—˜™š›œžŸ]{3}/u';
        
        return preg_replace_callback($pattern, function($matches) {
            return mb_convert_encoding($matches[0], 'Windows-1252', 'UTF-8');
        }, $decoded);
    }
}
