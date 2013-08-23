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
     * Les logs sont envoyés vers firebug.
     *
     * Utilisé pour le rendu en navigateur.
     */
    const DESTINATION_FIREBUG = 1;

    /**
     * Classe de log de Zend.
     *
     * @var Zend_Log
     */
    protected $_zendLogger;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Activation du log d'erreur (activé par défaut).
     *
     * @var bool
     */
    protected $_activation = true;

    /**
     * Log dans Firebug.
     *
     * @var bool
     */
    protected $_firebug = false;


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

    /**
     * Constructeur.
     */
    protected function __construct()
    {
        $this->_zendLogger = new Zend_Log();
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Ajoute une destination pour les logs.
     *
     * Lorsqu'une opération de log (ou de var_dump) est demandée, elle sera
     * effectuée sur toutes les sorties qui ont été ajoutées avec cette méthode.
     *
     * @param int $destination
     */
    public function addDestinationLogs($destination)
    {
        // Firebug
        if ($destination == self::DESTINATION_FIREBUG) {
            $this->_firebug = true;
            $writer = new Zend_Log_Writer_Firebug();
            // Pour afficher une table dans firebug
            $writer->setPriorityStyle(8, 'TABLE');
            // Ajoute la destination au Zend log
            $this->_zendLogger->addWriter($writer);
            // Pour afficher une table dans firebug
            $this->_zendLogger->addPriority('TABLE', 8);
        }
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
        $this->_zendLogger->log($var, Zend_Log::DEBUG);
    }

    /**
     * Log d'une exception.
     *
     * @param Exception $e
     */
    public function logException($e)
    {
        // Activation
        if ($this->_activation == false) {
            return;
        }
        $this->_zendLogger->err($e);
        $this->logger->error($e->getMessage(), ['exception' => $e]);
    }

    /**
     * Log une erreur
     *
     * @param string $message
     * @deprecated
     */
    public function error($message)
    {
        $this->logger->error($message);
    }

    /**
     * Log un warning
     *
     * @param string $message
     * @deprecated
     */
    public function warning($message)
    {
        $this->logger->warning($message);
    }

    /**
     * Log un message d'information
     *
     * @param string $message
     * @deprecated
     */
    public function info($message)
    {
        $this->logger->info($message);
    }

    /**
     * Log un message de debug
     *
     * @param string $message
     * @deprecated
     */
    public function debug($message)
    {
        $this->logger->debug($message);
    }

    /**
     * Envoie les headers.
     *
     * Ceci est nécessaire pour garantir que le débuggage dans Firebug est possible
     * en cas d'erreur. Cette fonction n'enverra donc les headers seulement
     * si le log a pour destination Firebug.
     */
    public function flushHeaders()
    {
        if ($this->_firebug) {
            Zend_Wildfire_Channel_HttpHeaders::getInstance()->flush();
            $response = Zend_Controller_Front::getInstance()->getResponse();
            if ($response) {
                $response->sendHeaders();
            }
        }
    }

}
