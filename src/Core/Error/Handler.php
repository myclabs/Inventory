<?php

use Psr\Log\LoggerInterface;

/**
 * Gestion des erreurs et des exceptions
 *
 * @author matthieu.napoli
 */
class Core_Error_Handler
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Gestionnaire d'exception.
     *
     * @param Exception $e
     *
     * @return void
     */
    public function myExceptionHandler($e)
    {
        try {
            // Log de l'erreur
            $this->logger->error($e->getMessage(), ['exception' => $e]);
            exit(1);
        } catch (Exception $exception) {
            $message = "Erreur dans le gestionnaire d'exception<br>";
            $message .= "<strong>Exception originale :</strong> <br>";
            $message .= "<strong>".$e->getMessage()."</strong> <br>";
            $message .= nl2br($e);
            $message .= "<br><br>Exception dans le gestionnaire d'exception :<br>";
            $message .= nl2br($exception);
            die($message);
        }
    }


    /**
     * Gestionnaire d'erreur.
     *
     * Cette fonction ne reçoit pas les erreurs fatales.
     *
     * @param int    $errno   Code de l'erreur.
     * @param string $errstr  Message de l'erreur.
     * @param string $errfile Fichier contenant l'erreur.
     * @param int    $errline Numéro de ligne où apparaît l'erreur.
     *
     * @throws ErrorException
     * @return bool Renvoie true une fois l'erreur capturée.
     */
    public function myErrorHandler($errno = 0, $errstr = null, $errfile = null, $errline = null)
    {
        // Ne log pas l'erreur (cas où une fonction est précédée de
        // l'opérateur @ (qui met temporairement error_reporting à 0).
        if (error_reporting() == 0) {
            return true;
        }

        $e = new ErrorException("$errstr in $errfile line $errline", 0, $errno, $errfile, $errline);

        // Lance une exception pour les erreurs (pas pour les notices ou autre)
        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                // Log de l'erreur
                $this->logger->warning($e->getMessage(), ['exception' => $e]);
                // Ne pas exécuter le gestionnaire interne de PHP.
                return true;
            default:
                // Transforme l'erreur en exception.
                throw $e;
        }
    }

    /**
     * Fonction appelée à la fin de l'exécution du script.
     *
     * Affiche les erreurs fatales.
     *
     * @return void
     */
    public function myShutdownFunction()
    {
        // Teste si il y'a eu une erreur fatale.
        $isError = false;
        $error = error_get_last();
        if ($error) {
            switch($error['type']) {
                case E_ERROR:
                case E_CORE_ERROR:
                case E_USER_ERROR:
                case E_COMPILE_ERROR:
                    $isError = true;
                    break;
            }
        }
        // Si il y'a eu une erreur fatale.
        if ($isError) {
            // Transforme l'erreur en exception.
            $e = new ErrorException($error['message'], 0, $error['type'], $error['file'], $error['line']);
            // Log de l'erreur
            $this->logger->error($e->getMessage(), ['exception' => $e]);
        }
    }

}
