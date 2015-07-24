<?php
/**
 * @copyright ©2015 Quicken Loans Inc. All rights reserved. Trade Secret,
 *    Confidential and Proprietary. Any dissemination outside of Quicken Loans
 *    is strictly prohibited.
 */

namespace QL\Panthor\ErrorHandling;

use Exception;

trait StacktraceFormatterTrait
{
    /**
     * @type string
     */
    private $root;

    /**
     * @type string
     */
    private $logStacktraces = false;

    /**
     * @param bool $enableLogging
     *
     * @return void
     */
    public function setStacktraceLogging($enableLogging)
    {
        $this->logStacktraces = (bool) $enableLogging;
    }

    /**
     * @param Exception $exception
     *
     * @return string
     */
    private function formatStacktrace(Exception $exception)
    {
        $this->root = $this->findApplicationRoot();

        $trace = $this->formatStacktraceEntry('ERR', [
            'function' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine()
        ]);

        if (!$this->logStacktraces) {
            return $trace;
        }

        foreach ($exception->getTrace() as $index => $entry) {
            $trace .= $this->formatStacktraceEntry(sprintf('#%d', $index), $entry);
        }

        return $trace;
    }

    /**
     * @param string $index
     * @param array $entry
     *
     * @return string
     */
    private function formatStacktraceEntry($index, array $entry)
    {
        $entry = array_replace([
            'file' => '',
            'line' => '?',
            'function' => '',
            'type' => '',
            'class' => '',
            'args' => [],
        ], $entry);

        if ($entry['class']) {
            $function = $entry['class'] . $entry['type'] . $entry['function'];
            $args = $entry['args'];
            array_walk($args, function(&$v) {
                $v = is_object($v) ? get_class($v) : gettype($v);
            });
            $function .= sprintf('(%s)', implode(', ', $args));
        } else {
            $function = $entry['function'];
        }

        $label = str_pad($index, 3);

        $file = $entry['file'] ? sprintf('%s:%s', $entry['file'], $entry['line']) : '[internal function]';
        $file = ($this->root) ? str_replace($this->root, '', $file) : $file;

        $entry = <<<TEXT
$label $file
    $function
TEXT;

        return $entry . str_repeat(PHP_EOL, 2);
    }

    /**
     * @return string
     */
    private function findApplicationRoot()
    {
        $current = __DIR__;
        if ($cut = strpos($current, 'vendor')) {
            return substr($current, 0, $cut);
        }

        return '';
    }
}
