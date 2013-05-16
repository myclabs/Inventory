<?php
/**
 * @package    Core
 * @subpackage Cache
 */

/**
 * Cache.
 *
 * @package    Core
 * @subpackage Cache
 *
 * @uses Zend_Cache
 */
abstract class Core_Cache extends Zend_Cache
{
    /**
     * Frontend utilisé par défaut.
     * @see Zend_Cache_Core
     *
     * @var string
     */
    protected static $_frontendDefault = 'Core';

    /**
     * Backend utilisé par défaut.
     * @see Zend_Cache_Backend_File
     *
     * @var string
     */
    protected static $_backendDefault = 'File';

    /**
     * Options du frontend par défaut.
     * @see Zend_Cache_Core
     *
     * @var array $_frontendOptionsDefault
     */
    protected static $_frontendOptionsDefault = array(
        'write_control'             => true,
        'caching'                   => true,
        'cache_id_prefix'           => 'Core_',
        'automatic_serialization'   => true,
        'automatic_cleaning_factor' => 10,
        'lifetime'                  => null,
        'logging'                   => false,
        'logger'                    => null,
        'ignore_user_abort'         => false
    );

    /**
     * Options du backend par défaut.
     * @see Zend_Cache_Backend_File
     *
     * @var array $_backendOptionsDefault
     */
    protected static $_backendOptionsDefault = array(
        'cache_dir' => './cache/',
        'file_locking' => true,
        'read_control' => true,
        'read_control_type' => 'adler32',
        'hashed_directory_level' => 0,
        'hashed_directory_umask' => 0700,
        'file_name_prefix' => 'Core_Cache',
        'cache_file_umask' => 0600,
        'metadatas_array_max_size' => 100
    );

    /**
     * Factory.
     *
     * @see Zend_Cache::factory
     *
     * @param string $directoryName nom du répertoire d'enregistrement du cache (sans '/' au début et à la fin).
     * @param string $frontend nom du frontend
     * @param string $backend nom du backend
     * @param array $frontendOptions options du frontend
     * @param array $backendOptions options du backend
     * @param bool $customFrontendNaming
     * @param bool $customBackendNaming
     * @param bool $autoload
     *
     * @return Zend_Cache_Core | Zend_Cache_Frontend | false en cas d'erreur.
     */
    public static function factory ($directoryName = '', $frontend = null, $backend = null,
                                    $frontendOptions = array(), $backendOptions = array(),
                                    $customFrontendNaming = false, $customBackendNaming = false, $autoload = false)
    {
        try {
            if ($frontend === null) {
                $frontend = self::$_frontendDefault;
            }
            if ($backend === null) {
                $backend = self::$_backendDefault;
            }

            $frontendOptions = $frontendOptions + self::$_frontendOptionsDefault;
            $backendOptions = $backendOptions + self::$_backendOptionsDefault;

            if ($directoryName === '' && $backend === 'File') {
                throw new Core_Exception_UndefinedAttribute('Définir un nom de répertoire où enregistrer le cache');
            }
            else {
                $dirpath = APPLICATION_PATH.'/../public/cache/'.$directoryName.'/';
                if (!file_exists($dirpath)) {
                    mkdir($dirpath, 0777, true);
                }
                $backendOptions['cache_dir'] = $dirpath;
            }

            return parent::factory($frontend, $backend, $frontendOptions, $backendOptions,
                            $customFrontendNaming, $customBackendNaming, $autoload);
        }
        catch (Core_Exception $e) {
            return false;
        }
        catch (Exception $e) {
            return false;
        }

    }


}//end class