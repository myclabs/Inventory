<?php

use Psr\Log\LoggerInterface;

/**
 * Log des erreurs et des exceptions.
 *
 * @author matthieu.napoli
 */
class Core_Error_Log
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Renvoie l'instance Singleton de la classe.
     *
     * @return Core_Error_Log
     */
    public static function getInstance()
    {
        static $instance = null;
        if (! $instance) {
            $instance = new self();
        }
        return $instance;
    }

    protected function __construct()
    {
        $this->_zendLogger = new Zend_Log();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Dump d'une variable.
     *
     * La variable est affichée dans les destinations enregistrées.
     *
     * @param mixed $var Variable à afficher.
     */
    public function dump($var)
    {
        $this->logger->debug('', ['var' => $var]);
    }

}
