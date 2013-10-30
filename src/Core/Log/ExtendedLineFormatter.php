<?php

namespace Core\Log;

use Monolog\Formatter\NormalizerFormatter;

/**
 * Log formatter alternative to Monolog\Formatter\LineFormatter
 *
 * @author matthieu.napoli
 */
class ExtendedLineFormatter extends NormalizerFormatter
{
    const SIMPLE_FORMAT = "[%datetime%] %channel%.%level_name%: %message% %extra%\n%exception%";

    protected $format;

    /**
     * @param string $format     The format of the message
     * @param string $dateFormat The format of the timestamp: one supported by DateTime::format
     */
    public function __construct($format = null, $dateFormat = null)
    {
        $this->format = $format ?: static::SIMPLE_FORMAT;
        parent::__construct($dateFormat);
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $vars = parent::format($record);

        if (isset($vars['context']['exception'])) {
            $vars['exception'] = $vars['context']['exception'];
            unset($vars['context']['exception']);
        } else {
            $vars['exception'] = null;
        }

        $output = $this->format;
        foreach ($vars['extra'] as $var => $val) {
            if (false !== strpos($output, '%extra.'.$var.'%')) {
                $output = str_replace('%extra.'.$var.'%', $this->convertToString($val), $output);
                unset($vars['extra'][$var]);
            }
        }
        foreach ($vars as $var => $val) {
            $output = str_replace('%'.$var.'%', $this->convertToString($val), $output);
        }

        return $output;
    }

    public function formatBatch(array $records)
    {
        $message = '';
        foreach ($records as $record) {
            $message .= $this->format($record);
        }

        return $message;
    }

    protected function normalize($data)
    {
        if (is_bool($data) || is_null($data)) {
            return var_export($data, true);
        }

        if ($data instanceof \Exception) {
            return $this->dumpException($data);
        }

        return parent::normalize($data);
    }

    protected function convertToString($data)
    {
        if (null === $data || is_scalar($data)) {
            return (string) $data;
        }

        if (is_array($data) && empty($data)) {
            return '';
        }

        $data = $this->normalize($data);
        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return $this->toJson($data);
        }

        return str_replace('\\/', '/', json_encode($data));
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
