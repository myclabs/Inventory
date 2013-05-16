<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage ORM
 */

use Doctrine\DBAL\DBALException;

/**
 * Class representing a Not Null constraint violation
 * @package    Core
 * @subpackage ORM
 */
class Core_ORM_NotNullViolationException extends DBALException
{

    /**
     * Name of the DB column
     * @var string
     */
    protected $column;


    /**
     * @param string         $column   Name of the DB column
     * @param Exception|null $previous Previous exception
     */
    public function __construct($column, Exception $previous = null)
    {
        $previousMessage = $previous ? $previous->getMessage() : '';
        $message = "Column '$column' cannot be null."
            . PHP_EOL . PHP_EOL . $previousMessage;
        parent::__construct($message, 0, $previous);
        $this->column = $column;
    }

    /**
     * @return string Name of the DB key
     */
    public function getColumn()
    {
        return $this->column;
    }

}
