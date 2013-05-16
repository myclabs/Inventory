<?php
/**
 * @author matthieu.napoli
 * @author valentin.claras
 * @package    Core
 * @subpackage Script
 */

/**
 * Class for Build scripts
 *
 * A build script launches actions (Core_Script_Action)
 *
 * @package    Core
 * @subpackage Script
 */
class Core_Script_Build
{
    /**
     * List all environments usable as option.
     *
     * @var array
     */
    protected $acceptedEnvironments = array('testsunitaires', 'developpement', 'test', 'production');

    /**
     * Actions available in build/ directory.
     *
     * @var array(actionName => Core_Script_Action)
     */
    protected $_availableActions;

    /**
     * Actions available in build/ directory of the dependencies.
     *
     * @var array(packageName => Core_Package)
     */
    protected $_dependencies;

    /**
     * Actions available in build/ directory of the dependencies.
     *
     * @var array(packageName => array(actionName => Core_Script_Action))
     */
    protected $_availableActionsDependencies = array();

    /**
     * Console parser.
     *
     * @var Console_CommandLine
     */
    protected $_parser;

    /**
     * Constructor.
     *  Define the CLI interface.
     *
     * @return void
     */
    public function __construct()
    {
        // Get package.
        $package = Core_Package_Manager::getCurrentPackage();
        // Available actions.
        $this->_availableActions = $this->getActions($package);
        // Available actions in the dependencies.
        $this->_dependencies = Core_Package_Manager::getAllDependencies();
        foreach ($this->_dependencies as $dependency) {
            try {
                $actions = $this->getActions($dependency);
            } catch (Exception $e){
                $actions = array();
                echo 'Notice: No actions for '.$dependency->getName().PHP_EOL;
                echo "\t".$e->getMessage().PHP_EOL;
                echo '---------------------------'.PHP_EOL;
            }
            $this->_availableActionsDependencies[$dependency->getName()] = $actions;
        }
        // CLI parser.
        $this->_parser = new Console_CommandLine(array(
            'name' => "php build.php",
            'description' => "Run the build script.",
        ));
        $actionChoice = implode(', ', array_keys($this->_availableActions));
        $this->_parser->addArgument('actions', array(
            'description' => "Actions can be $actionChoice.",
            'multiple' => true,
            'optional' => false,
        ));
        $this->_parser->addOption('environment', array(
            'short_name'  => '-e',
            'long_name'   => '--environment',
            'description' => "Environments to use for Create action. Example: '-e developpement'. "
                            ."Default is '-e testsunitaires,developpement'",
            'default'     => 'testsunitaires,developpement',
        ));
        $this->_parser->addOption('targetPackage', array(
            'short_name'  => '-t',
            'long_name'   => '--targetPackage',
            'description' => "Target package name where the actions can be found. Example: '-t Log'. "
                             ."Default is '-t current_package'",
            'default'     => $package->getName(),
        ));
    }

    /**
     * Run all the actions in the build directory
     *
     * @return void
     */
    public function run()
    {
        $result = $this->_parser->parse();

        // Options.
        $environments   = $this->parseEnvironmentOption($result->options['environment']);
        $currentPackage = Core_Package_Manager::getCurrentPackage();
        $targetPackageName = $result->options['targetPackage'];

        // Get the action list.
        if ($targetPackageName != $currentPackage->getName()) {
            if (!array_key_exists($targetPackageName, $this->_availableActionsDependencies)) {
                throw new Core_Exception_InvalidArgument(
                    'The target package is not in the dependencies list of the current package.'
                );
            }
            // Actions.
            $actions = array();
            foreach ($result->args['actions'] as $actionName) {
                if (! isset($this->_availableActionsDependencies[$targetPackageName][$actionName])) {
                    $actionChoice = implode(', ', array_keys($this->_availableActionsDependencies[$targetPackageName]));
                    throw new Core_Exception_InvalidArgument(
                        'Action "'.$actionName.'" doesn\'t exist. '.
                        'Possible values for the actions of the package '.$targetPackageName.' are '.$actionChoice.'.'
                    );
                }
                $actions[$actionName] = $this->_availableActionsDependencies[$targetPackageName][$actionName];
            }
        } else {
            // Actions.
            $actions = array();
            foreach ($result->args['actions'] as $actionName) {
                if (! isset($this->_availableActions[$actionName])) {
                    $actionChoice = implode(', ', array_keys($this->_availableActions));
                    throw new Core_Exception_InvalidArgument("Action '$actionName' doesn't exist. "
                        ."Possible values for the actions are $actionChoice.");
                }
                $actions[$actionName] = $this->_availableActions[$actionName];
            }
        }

        // Run the actions.
        /* @var $action Core_Script_Action */
        foreach ($actions as $actionName => $action) {
            // Action.
            $action->dynamicEnvironments = $environments;
            echo $targetPackageName.' > '.$actionName.PHP_EOL;
            $action->run();
        }
    }

    /**
     * Parse the environment option.
     *
     * @param  string  $value Value given in CLI for the environment option.
     *
     * @return array() Array of the multiple environments.
     */
    private function parseEnvironmentOption($value)
    {
        $tab = explode(',', $value);

        foreach ($tab as $environment) {
            if (!(in_array($environment, $this->acceptedEnvironments))) {
                throw new Core_Exception_InvalidArgument(
                    'Possible values for -e are '.implode(',', $this->acceptedEnvironments).'. '.
                    'Values should be separated by a comma.');
            }
        }

        return $tab;
    }

    /**
     * Returns the actions that exists in the build/ directory of the package
     *
     * @param Core_Package $package Package
     *
     * @return array(actionName => Core_Script_Action)
     */
    private function getActions(Core_Package $package)
    {
        $actions = array();
        $basePath = $package->getPath().'/scripts/build';
        // Find scripts in subdirectories.
        if (! is_dir($basePath)) {
            throw new Exception("The directory '$basePath' doesn't exist");
        }
        $files = scandir($basePath);
        foreach ($files as $file) {
            if (is_dir($basePath.'/'.$file) && ($file != '.') && ($file != '..') && ($file != '.svn')) {
                $scriptFilename = $basePath.'/'.$file.'/'.$file.'.php';
                // Check if file exists.
                if (! file_exists($scriptFilename)) {
                    throw new Exception(
                        "The script folder '$file' exists, but the file '$scriptFilename' was not found"
                    );
                }
                require_once $scriptFilename;
                // Construct classname.
                $classname = $package->getName().'_'.ucfirst($file);
                // Check if class exists
                if (! class_exists($classname)) {
                    throw new Exception(
                        "The script file '$scriptFilename' exists, but the class '$classname' was not found inside it"
                    );
                }
                // Load the action class.
                $instance = new $classname();
                $instance->dynamicPackage = $package;
                // Check if class extends Core_Script_Action
                if (! $instance instanceof Core_Script_Action) {
                    throw new Exception(
                        "The class '$classname' doesn't extend Core_Script_Action"
                    );
                }
                // Store the action class.
                $actions[$file] = $instance;
            }
        }
        return $actions;
    }

}
