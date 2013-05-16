<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Profiler
 */

/**
 * Log des requêtes SQL dans un fichier
 *
 * @package    Core
 * @subpackage Profiler
 */
class Core_Profiler_File implements Doctrine\DBAL\Logging\SQLLogger
{
    /**
     * Fichier de log.
     *
     * Chemin relatif au dossier "application".
     *
     * @var string
     */
    protected $_fichierLog = '/../data/logs/queries.log';

    /**
     * Pointeur vers le fichier ouvert.
     */
    protected $_fichier;


    /**
     * Constructeur : Initialise le fichier de Log..
     */
    public function __construct()
    {
        // Teste si le dossier existe.
        if (! is_dir(dirname(APPLICATION_PATH.$this->_fichierLog))) {
            if (! mkdir(dirname(APPLICATION_PATH.$this->_fichierLog), 0777, true)) {
                Core_Error_Log::getInstance()->dump('Cannot use query profiler, dir "data/logs/" does not exist.');
                return;
            }
        }
        // Teste s'il est possible d'écrire dans le fichier.
        if (! is_writable(APPLICATION_PATH.$this->_fichierLog)) {
            if (!touch(APPLICATION_PATH.$this->_fichierLog)) {
                Core_Error_Log::getInstance()->dump('Cannot use query profiler, file "queries.log" is not writable.');
                return;
            }
        }

        // Création du log
        $this->_fichier = fopen(APPLICATION_PATH.$this->_fichierLog, 'w');
    }

    /**
     * Logs a SQL statement somewhere.
     * {@inheritdoc}
     *
     * @param string $sql The SQL to be executed.
     * @param array $params The SQL parameters.
     * @param array $types The SQL parameter types.
     * @return void
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $text = 'Query : '.$sql."\n\t".' with parameters : '. json_encode($params).' of type '.json_encode($types);
        // Ecrit la requête dans le fichier
        fputs($this->_fichier, $text."\n\n");
    }

    /**
     * Mark the last started query as stopped. This can be used for timing of queries.
     * {@inheritdoc}
     *
     * @return void
     */
    public function stopQuery()
    {
    }

}
