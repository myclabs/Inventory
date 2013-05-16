<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Error
 */

/**
 * Gestion des erreurs et des exceptions
 *
 * @package    Core
 * @subpackage Error
 */
class Core_Error_Handler
{

    /**
     * Gestionnaire d'exception.
     *
     * @param Exception $e
     *
     * @return void
     */
    public static function myExceptionHandler($e)
    {
        try {
            // Log de l'erreur.
            Core_Error_Log::getInstance()->logException($e);
            // Envoi des headers (sinon aucun message d'erreur ne parvient à firebug).
            Core_Error_Log::getInstance()->flushHeaders();
            exit(1);
        } catch (Zend_Wildfire_Exception $exception) {
            $message = "<strong>".$e->getMessage()."</strong> <br>";
            $message .= nl2br($e);
            $message .= "<br><br>Impossible d'envoyer cette exception via Firebug car les";
            $message .= " objets 'Header' n'ont pas encore été créés (erreur dans le Bootstrap)";
            die ($message);
        } catch (Exception $exception) {
            $message = "Erreur dans le gestionnaire d'exception<br>";
            $message .= "<strong>Exception originale :</strong> <br>";
            $message .= "<strong>".$e->getMessage()."</strong> <br>";
            $message .= nl2br($e);
            $message .= "<br><br>Exception dans le gestionnaire d'exception :<br>";
            $message .= nl2br($exception);
            die ($message);
        }
    }


    /**
     * Gestionnaire d'erreur.
     *
     * Cette fonction ne reçoit pas les erreurs fatales.
     *
     * @param int 	 $errno   Code de l'erreur.
     * @param string $errstr  Message de l'erreur.
     * @param string $errfile Fichier contenant l'erreur.
     * @param int	 $errline Numéro de ligne où apparaît l'erreur.
     *
     * @return bool Renvoie true une fois l'erreur capturée.
     */
    public static function myErrorHandler($errno = 0, $errstr = null, $errfile = null, $errline = null)
    {
        // Ne log pas l'erreur (cas où une fonction est précédée de
        // l'opérateur @ (qui met temporairement error_reporting à 0).
        if (error_reporting() == 0) {
            return true;
        }
        // Lance une exception pour les erreurs (pas pour les notices ou autre)
        switch ($errno) {
            case E_NOTICE:
            case E_USER_NOTICE:
            case E_STRICT:
            case E_DEPRECATED:
            case E_USER_DEPRECATED:
                // Log de l'erreur
                Core_Error_Log::getInstance()->warning("$errstr in $errfile line $errline");
                $e = new ErrorException("$errstr in $errfile line $errline", 0, $errno, $errfile, $errline);
                Core_Error_Log::getInstance()->logException($e);
                // Ne pas exécuter le gestionnaire interne de PHP.
                return true;
            default:
                // Transforme l'erreur en exception.
                throw new ErrorException("$errstr in $errfile line $errline", 0, $errno, $errfile, $errline);
        }
    }

    /**
     * Fonction appelée à la fin de l'exécution du script.
     *
     * Affiche les erreurs fatales.
     *
     * @return void
     */
    public static function myShutdownFunction()
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
            // Log de l'exception.
            Core_Error_Log::getInstance()->logException($e);
            // Envoi des headers (sinon aucun message d'erreur ne parvient à firebug).
            Core_Error_Log::getInstance()->flushHeaders();
        }
    }

}
