<?php

namespace App\Admin\Showcase;

use Dskripchenko\LaravelAdmin\Layout\Code;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use ReflectionClass;
use ReflectionMethod;

/**
 * "How it's built": a screen shows its own PHP next to the result.
 *
 *     public function layout(): array
 *     {
 *         return [...$this->demo(), $this->sourceCode()];          // the whole class
 *         return [...$this->demo(), $this->sourceCode('demo')];    // one method
 *     }
 *
 * The source is read through reflection, so what the visitor sees is exactly
 * the code that produced the page. Files are read once per process.
 */
trait ShowsSource
{
    /** @var array<string, list<string>> file => lines */
    private static array $sourceFiles = [];

    /**
     * A highlighted, copyable block with the source of this class, or of one
     * of its methods (with its doc comment).
     */
    protected function sourceCode(?string $method = null, ?int $maxHeight = 560): Code
    {
        $class = new ReflectionClass($this);
        $file = (string) $class->getFileName();
        $lines = self::$sourceFiles[$file] ??= file($file, FILE_IGNORE_NEW_LINES) ?: [];

        if ($method === null) {
            $code = implode("\n", $lines);
            $title = $this->relativePath($file);
        } else {
            $reflection = new ReflectionMethod($this, $method);
            $start = $reflection->getStartLine();
            $doc = $reflection->getDocComment();
            if ($doc !== false) {
                $start -= substr_count($doc, "\n") + 1;
            }
            $slice = array_slice($lines, $start - 1, $reflection->getEndLine() - $start + 1);
            $code = $this->dedent($slice);
            $title = $this->relativePath($file).' › '.$method.'()';
        }

        $block = Layout::code($code, 'php')->title($title)->lineNumbers();

        return $maxHeight !== null ? $block->maxHeight($maxHeight) : $block;
    }

    private function relativePath(string $file): string
    {
        return ltrim(str_replace(base_path(), '', $file), '/');
    }

    /** @param list<string> $lines */
    private function dedent(array $lines): string
    {
        $indent = PHP_INT_MAX;
        foreach ($lines as $line) {
            if (trim($line) !== '') {
                $indent = min($indent, strlen($line) - strlen(ltrim($line)));
            }
        }

        return implode("\n", array_map(fn (string $line) => substr($line, $indent === PHP_INT_MAX ? 0 : $indent), $lines));
    }
}
