<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @package Algo
 */

/**
 * @package Algo
 */
class Algo_ConfigError
{

    /**
     * Flag permettant de préciser si l'error est fatale ( = bloquante) ou non.
     * @var boolean
     */
    protected $fatal;

    /**
     * Le message décrivant l'erreur.
     * @var string
     */
    protected $message;


    /**
     * @param string $message Message de l'erreur
     * @param bool   $isFatal Flag permettant de préciser si l'error est fatale (bloquante) ou non.
     */
    public function __construct($message = null, $isFatal = null)
    {
        $this->message = $message;
        $this->fatal = $isFatal;
    }

    /**
     * Return the resultType of the algo.
     * @return boolean
     */
    public function getFatal()
    {
        return $this->fatal;
    }

    /**
     * Flag permettant de préciser si l'error est fatale (bloquante) ou non.
     * @param boolean $fatal
     */
    public function isFatal($fatal)
    {
        $this->fatal = (boolean) $fatal;
    }

    /**
     * Return the message of the error.
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }


    /**
     * Set the error's message.
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = (string) $message;
    }

}
