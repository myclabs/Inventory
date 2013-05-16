<?php
/**
 * @author     alexandre.delorme
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Package
 */

require_once realpath(dirname(__FILE__).'/../Autoloader.php');

/**
 * Classe gérant les dépendances des packages
 *
 * @package    Core
 * @subpackage Package
 */
class Core_Package_Manager
{

    /**
     * Description of the package being run
     * @var Core_Package
     */
    protected static $_package = null;

    /**
     * Description of the dependencies (other packages)
     * @var array(packageName => Core_Package)
     */
    protected static $_dependencies = array();

    /**
     * Returns the package being run
     *
     * @return Core_Package
     */
    public static function getCurrentPackage()
    {
        return self::$_package;
    }

    /**
     * Returns the dependencies packages
     *
     * @return array(packageName => Core_Package)
     */
    public static function getAllDependencies()
    {
        return self::$_dependencies;
    }

    /**
     * Returns the package description
     *
     * @param string $packageName Example: 'Core', or 'UI'
     *
     * @return Core_Package
     */
    public static function getPackage($packageName)
    {
        if (self::$_package->getName() == $packageName) {
            return self::$_package;
        }
        if (isset(self::$_dependencies[$packageName])) {
            $package = self::$_dependencies[$packageName];
            if ($package->getName() == $packageName) {
                return $package;
            }
        }
        throw new Core_Exception_InvalidArgument("The package '$packageName' was not registred");
    }

    /**
     * Initialise le package courant
     *
     * Voir le fichier de configuration /application/configs/package.php
     */
    public static function initPackage()
    {
        $autoloader = Core_Autoloader::getInstance();
        // Inclut les infos sur le package
        require_once PACKAGE_PATH . '/application/configs/package.php';
        if ((! isset($packageName)) || (! isset($packageIsModule)) || (! isset($pathsToInclude))) {
            die("File 'application/configs/package.php' is not properly configured.");
        }
        // Module du package
        if ($packageIsModule) {
            $autoloader->addModule($packageName, APPLICATION_PATH.'/'.strtolower($packageName));
        }
        // Dossiers à inclure
        if (! empty($pathsToInclude)) {
            foreach ($pathsToInclude as $path) {
                $autoloader->addNamespace($packageName, PACKAGE_PATH.'/'.$path);
            }
        }

        // Traduction
        if (! Zend_Registry::isRegistered(Core_Translate::registryKey)) {
            Zend_Registry::set(Core_Translate::registryKey, new Core_Translate());
        }
        if (! Zend_Registry::isRegistered(Core_Locale::registryKey)) {
            Zend_Registry::set(Core_Locale::registryKey, Core_Locale::loadDefault());
        }
        // Inclusion des textes - inutile car les textes de l'application sont chargés
        // lors du construct de Core_Translate
        //$translate = Zend_Registry::get(Core_Translate::registryKey);
        //$translate->addModule($packageName, PACKAGE_PATH);

        // Enregistrement du package
        self::$_package = new Core_Package($packageName, PACKAGE_PATH);
    }

    /**
     * Charge les dépendances relatives au package
     *
     * @param array $dependencies Tableau des dépendances
     */
    public static function loadDependencies(array $dependencies)
    {
        if (empty($dependencies)) {
            // No dependencies.
            return;
        }
        $autoloader = Core_Autoloader::getInstance();

        $appLibraryPath = APPLICATION_PATH.'/../library';

        $finalDeps = array();

        // Vérification de la compatibilité des dépendances.
        foreach ($dependencies as $package => $version) {
            // on vérifie d'abord si le répertoire existe.
            self::getPackageDirectory($package, $version);

            if (! array_key_exists($package, $finalDeps)) {
                $finalDeps[$package] = $version;
            } elseif ($finalDeps[$package] == $version) {
                continue;
            } elseif ($finalDeps[$package] !== $version) {
                die ("Conflit de dependances sur le package '".$package."' $version != " . $finalDeps[$package]);
            }

            $finalDeps = self::getRecursDependencies($package, $version, $finalDeps);
        }

        // tableau d'objet de type Dependency.
        $depsToRegister = array();

        // Chargement des packages.
        foreach ($finalDeps as $package => $version) {
            $packageDir = self::getPackageDirectory($package, $version);
            $packagePath = realpath(LIBRARY_PATH . '/' . $packageDir);
            $packagePublicURL = realpath(LIBRARY_URL . '/' . $packageDir . '/public/');

            require_once $packagePath . '/application/configs/package.php';

            // Chargement des modules.
            if ($packageIsModule) {
                $autoloader->addModule($package, $packagePath.'/application/'.strtolower($package));
            }

            // Chargement des classes.
            if (! empty($pathsToInclude)) {
                foreach ($pathsToInclude as $path) {
                    $autoloader->addNamespace($package, $packagePath.'/'.$path);
                }
            }

            // Enregistrement de la dépendance dans un objet Dependency.
            $dep = new Core_Package($package, $packagePath, $version, $packagePublicURL, $packageIsModule);
            $depsToRegister[$package] = $dep;

            // Traduction
            if (! isset($translate)) {
                $translate = new Core_Translate();
            }

            // Inclusion des textes
            $translate->addModule($package, $packagePath);
        }

        // Enregistrement du tableau des dépendances.
        self::$_dependencies = $depsToRegister;
        // Enregistrement de Translate dans le Zend_Registry.
        Zend_Registry::set(Core_Translate::registryKey, $translate);
        // Enregistrement de la locale dans le Zend_Registry.
        Zend_Registry::set(Core_Locale::registryKey, Core_Locale::loadDefault());
    }

    /**
     * Récupère les dépendances relatives à un package
     *
     * @param string $currentPackage nom du package
     * @param string $version 'trunk' ou version du package
     * @param array  $finalDeps dependances
     *
     * @return array $finalDeps dependances finales
     */
    protected static function getRecursDependencies($currentPackage, $version, $finalDeps)
    {
        $packagePath = LIBRARY_PATH . '/' . self::getPackageDirectory($currentPackage, $version);
        require_once $packagePath . '/application/configs/dependencies.php';

        if (! empty($dependencies)) {
            foreach ($dependencies as $package => $version) {
                // on vérifie d'abord si le répertoire existe.
                self::getPackageDirectory($package, $version);

                if (empty($finalDeps[$package])) {
                    $finalDeps[$package] = $version;
                } elseif ($finalDeps[$package] == $version) {
                    continue;
                } elseif ($finalDeps[$package] !== $version) {
                    die ("Conflit de dependances sur le package '".$package."' " . $finalDeps[$package] . " != "
                        .  $version . " (declared in $currentPackage)");
                }

                $finalDeps = self::getRecursDependencies($package, $version, $finalDeps);
            }
        }

        return $finalDeps;
    }

    /**
     * Vérifie si le répertoire du package existe et le retourne.
     *
     * @param string $package nom du package.
     * @param string $version 'trunk' ou version du package.
     *
     * @return string chemin du package.
     */
    protected static function getPackageDirectory($package, $version)
    {
        $packageDir = '';
        if ($version == 'trunk') {
            $packageDir = $package . '/trunk';
            if (! is_dir(LIBRARY_PATH . '/' . $packageDir)) {
                $messageErreur = 'Le repertoire du package "'.$package
                                .'" avec version "'.$version.'" est introuvable. '
                                .'Verifier les fichiers "dependencies.php".';
                die($messageErreur);
            }
        } else {
            $packageDir = $package . '/tags/' . $version;
            if (! is_dir(LIBRARY_PATH . '/' . $packageDir)) {
                $packageDir = $package . '/branches/' . $version;
                if (! is_dir(LIBRARY_PATH . '/' . $packageDir)) {
                    $messageErreur = 'Le repertoire du package "'.$package
                                    .'" avec version "'.$version.'" est introuvable. '
                                    .'Verifier les fichiers "dependencies.php".';
                    die($messageErreur);
                }
            }
        }
        return $packageDir;
    }

}
