<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage ORM
 */

use Doctrine\DBAL\DBALException;

/**
 * Class representing a DB duplicate entry for a key
 * @package    Core
 * @subpackage ORM
 */
class Core_ORM_DuplicateEntryException extends DBALException
{

    /**
     * Entry that created the exception
     * @var string
     */
    protected $entry;

    /**
     * Name of the DB key
     * @var string
     */
    protected $key;


    /**
     * @param string         $entry    Entry that created the exception
     * @param int            $key      Name of the DB key
     * @param Exception|null $previous Previous exception
     */
    public function __construct($entry, $key, Exception $previous = null)
    {
        $previousMessage = $previous ? $previous->getMessage() : '';
        $message = "Duplicate entry '$entry' for key '$key'."
            . PHP_EOL . PHP_EOL . $previousMessage;
        parent::__construct($message, 0, $previous);
        $this->entry = $entry;
        $this->key = $key;
    }

    /**
     * @return string Entry that created the exception
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * @return string Name of the DB key
     */
    public function getKey()
    {
        return $this->key;
    }

}
