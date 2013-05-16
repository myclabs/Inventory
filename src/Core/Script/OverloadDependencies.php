<?php
/**
 * @author     valentin.claras
 * @package    Core
 * @subpackage Script
 */

/**
 * Class for OverloadDependencies action
 *
 * @package    Core
 * @subpackage Script
 */
abstract class Core_Script_OverloadDependencies extends Core_Script_Action
{
    /**
     * List all environments where the action can be made.
     *
     * @var array
     */
    protected $acceptedEnvironments = array('developpement', 'test', 'production');

    /**
     * Association array of directory and file types.
     *
     * @var array
     */
    protected $dirTypeAssoc = array(
        'css'       => '\.(css)$',
        'scripts'   => '\.(js)$',
    );

    /**
     * Run the action for a specific environments.
     *
     * @param string $environment
     *
     * @return void
     */
    public function runEnvironment($environment)
    {
        if (in_array($environment, $this->acceptedEnvironments)) {
            $this->runLoad();
            $this->runOverload();
        }
    }

    /**
     * Run the script which copy the content of "public/" dependencies
     *
     * @return void
     */
    public function runLoad()
    {
        // Récupération de l'ensemble des dépendances.
        $dependencies = Core_Package_Manager::getAllDependencies();
        // Parcours des dépendances.
        foreach ($dependencies as $dependency) {
            $dependencyName = strtolower($dependency->getName());
            // Parcours de la liste des dossiers à copier.
            foreach ($this->dirTypeAssoc as $dirName => $fileTypes) {
                $srcPath = $dependency->getPath().'/public/'.$dirName.'/'.$dependencyName;
                $destPath = PACKAGE_PATH.'/public/'.$dirName.'/'.$dependencyName;
                // Copie du dossier.
                $this->copyDir($srcPath, $destPath, $fileTypes);
            }
        }
    }

    /**
     * Run the script which overload the content of "public/" dependencies
     *
     * @return void
     */
    public function runOverload()
    {
        // Récupération de l'ensemble des dépendances.
        $dependencies = Core_Package_Manager::getAllDependencies();
        // Parcours des dépendances.
        foreach ($dependencies as $dependency) {
            $dependencyName = strtolower($dependency->getName());
            // Parcours de la liste des dossiers à copier.
            foreach ($this->dirTypeAssoc as $dirName => $fileTypes) {
                $srcPath = PACKAGE_PATH.'/scripts/build/overloadDependencies/'.$dependencyName.'/'.$dirName;
                $destPath = PACKAGE_PATH.'/public/'.$dirName.'/'.$dependencyName;
                // Copie du dossier.
                $this->copyDir($srcPath, $destPath, $fileTypes);
            }
        }
    }

    /**
     * Copy the content of a dependency public directory to the current application directory
     *
     * @param string $srcPath       Source directory.
     * @param string $destPath      Destination directory.
     * @param string $fileNameTypes Mask for the regular expression which filter files type.
     *
     * @return void
     */
    protected function copyDir($srcPath, $destPath, $fileNameTypes)
    {
        // Vérification de l'existence du dossier.
        if (is_dir($srcPath)) {
            $directory = opendir($srcPath);
            if ($directory !== false) {
                // Si le répertoire existe, on vérifie que son homologue existe dans l'application.
                if (!(is_dir($destPath))) {
                    mkdir($destPath);
                }
                // Lecture du dossier de la dépendance.
                $fileName = readdir($directory);
                while ($fileName !== false) {
                    // Vérification qu'il s'agisse bien d'un fichier du bon type.
                    if (is_file($srcPath.'/'.$fileName) && (preg_match('#'.$fileNameTypes.'$#', $fileName))) {
                        // Copie du fichier.
                        copy($srcPath.'/'.$fileName, $destPath.'/'.$fileName);
                    }
                    // Lecture du fichier suivant.
                    $fileName = readdir($directory);
                }
            }
        }
    }

}
