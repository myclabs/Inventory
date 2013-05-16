<?php
/**
 * @author  matthieu.napoli
 * @package TEC
 */

/**
 * Exception due à une expression invalide
 * @package TEC
 */
class TEC_Model_InvalidExpressionException extends Core_Exception
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
