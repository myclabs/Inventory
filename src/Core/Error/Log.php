<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Error
 */

/**
 * Log des erreurs et des exceptions.
 *
 * @package    Core
 * @subpackage Error
 */
class Core_Error_Log extends Core_Singleton
{

    /**
     * Les logs ne sont envoyés nulle part.
     */
    const DESTINATION_NONE = 0;
    /**
     * Les logs sont envoyés vers firebug.
     *
     * Utilisé pour le rendu en navigateur.
     */
    const DESTINATION_FIREBUG = 1;
    /**
     * Les logs sont envoyés dans la console.
     *
     * Aucun formatage HTML n'est utilisé.
     */
    const DESTINATION_CONSOLE = 2;
    /**
     * Les logs sont envoyés dans la console.
     *
     * Aucun formatage HTML n'est utilisé.
     */
    const DESTINATION_FILE = 3;

    /**
     * Classe de log de Zend.
     *
     * @var Zend_Log
     */
    protected $_zendLogger;

    /**
     * Activation du log d'erreur (activé par défaut).
     *
     * @var bool
     */
    protected $_activation = true;

    /**
     * Fichier de log.
     *
     * Chemin relatif au dossier "application".
     *
     * @var string
     */
    protected $_fichierLog = '/../data/logs/error.log';

    /**
     * Log dans Firebug.
     *
     * @var bool
     */
    protected $_firebug = false;


    /**
     * Constructeur.
     */
    protected function __construct()
    {
        $this->_zendLogger = new Zend_Log();
    }

    /**
     * Active le log d'erreur (activé par défaut)
     */
    public function enable()
    {
        $this->_activation = true;
    }

    /**
     * Désactive le log d'erreur
     */
    public function disable()
    {
        $this->_activation = false;
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
        // Fichier
        if ($destination == self::DESTINATION_FILE) {
            // Teste si le dossier existe
            if (! is_dir(dirname(APPLICATION_PATH.$this->_fichierLog))) {
                if (! mkdir(dirname(APPLICATION_PATH.$this->_fichierLog), 0777, true)) {
                    $this->warning("Impossible d'activer le log des erreurs dans un fichier : "
                        . "le dossier 'data/logs/' n'existe pas.");
                    return;
                }
            }
            // Teste s'il est possible d'écrire dans le fichier
            if (! is_writable(APPLICATION_PATH.$this->_fichierLog)) {
                if (!touch(APPLICATION_PATH.$this->_fichierLog)) {
                    $this->warning("Impossible d'activer le log des erreurs dans un fichier : "
                        . "le fichier 'data/logs/error.log' ne peut pas être accédé en écriture.");
                    return;
                }
            }
            $writer = new Zend_Log_Writer_Stream(APPLICATION_PATH.$this->_fichierLog);
            $this->_zendLogger->addWriter($writer);
        }
        // Sortie PHP
        if ($destination == self::DESTINATION_CONSOLE) {
            $writer = new Zend_Log_Writer_Stream('php://output');
            $this->_zendLogger->addWriter($writer);
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
    }

    /**
     * Log une erreur
     *
     * @param string $message
     */
    public function error($message)
    {
        $this->_zendLogger->log($message, Zend_Log::ERR);
    }

    /**
     * Log un warning
     *
     * @param string $message
     */
    public function warning($message)
    {
        $this->_zendLogger->log($message, Zend_Log::WARN);
    }

    /**
     * Log un message d'information
     *
     * @param string $message
     */
    public function info($message)
    {
        $this->_zendLogger->log($message, Zend_Log::INFO);
    }

    /**
     * Log un message de debug
     *
     * @param string $message
     */
    public function debug($message)
    {
        $this->_zendLogger->log($message, Zend_Log::DEBUG);
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
