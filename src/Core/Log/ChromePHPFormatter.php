<?php

namespace Core\Log;

use Monolog\Logger;

/**
 * ChromePHPFormatter
 */
class ChromePHPFormatter extends \Monolog\Formatter\ChromePHPFormatter
{
    private $logLevels = array(
        Logger::DEBUG     => 'log',
        Logger::INFO      => 'info',
        Logger::NOTICE    => 'info',
        Logger::WARNING   => 'warn',
        Logger::ERROR     => 'error',
        Logger::CRITICAL  => 'error',
        Logger::ALERT     => 'error',
        Logger::EMERGENCY => 'error',
    );

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        // Retrieve the line and file if set and remove them from the formatted extra
        $backtrace = 'unknown';
        if (isset($record['extra']['file']) && isset($record['extra']['line'])) {
            $backtrace = $record['extra']['file'].' : '.$record['extra']['line'];
            unset($record['extra']['file']);
            unset($record['extra']['line']);
        }

        $message = $record['message'];

        if (isset($record['context']['exception'])) {
            $message .= "\n\n" . $this->dumpException($record['context']['exception']);
        }

        return array(
            $record['channel'],
            $message,
            $backtrace,
            $this->logLevels[$record['level']],
        );
    }

    private function dumpException(\Exception $e)
    {
        $text = get_class($e) . ': ' . $e->getMessage() . PHP_EOL
            . 'at ' . $e->getFile() . '(' . $e->getLine() . ')' . PHP_EOL
            . $e->getTraceAsString() . PHP_EOL;

        if ($e->getPrevious()) {
            $text .= 'Caused by ' . $this->dumpException($e->getPrevious());
        }

        return $text;
    }
}
