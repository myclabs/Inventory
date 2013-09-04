<?php
/**
 * @author     matthieu.napoli
 * @author     valentin.claras
 * @package    TEC
 * @subpackage Exception
 */

namespace TEC\Exception;

use Core_Exception;

/**
 * Exception due à une expression invalide
 * @package    TEC
 * @subpackage Exception
 */
class InvalidExpressionException extends Core_Exception
{

    /**
     * @var string
     */
    protected $expression;

    /**
     * @var string[]
     */
    protected $errors;

    /**
     * @param null|string $message    Message d'exception
     * @param string      $expression Expression complète
     * @param string[]    $errors     Erreurs dans l'expression
     */
    public function __construct($message, $expression, array $errors = [])
    {
        parent::__construct($message);
        $this->expression = $expression;
        $this->errors = $errors;
    }

    /**
     * @return string
     */
    public function getExpression()
    {
        return $this->expression;
    }

    /**
     * @return string[] Erreurs trouvées dans l'expression
     */
    public function getErrors()
    {
        return $this->errors;
    }

}
