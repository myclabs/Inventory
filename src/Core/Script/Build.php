<?php

namespace Core\Script;

use Console_CommandLine;
use Console_CommandLine_Result;
use Core_Exception_InvalidArgument;
use Core_Script_Action;
use Exception;

/**
 * Class for Build scripts
 *
 * A build script launches actions (Core_Script_Action)
 *
 * @author matthieu.napoli
 * @author valentin.claras
 */
class Build
{
    /**
     * List all environments usable as option.
     *
     * @var array
     */
    protected $acceptedEnvironments = array('testsunitaires', 'developpement', 'test', 'production');

    /**
     * @var Console_CommandLine_Result
     */
    private $result;

    /**
     * @var string
     */
    private $environment;

    /**
     * Define the CLI interface.
     */
    public function __construct()
    {
        // CLI parser.
        $parser = new Console_CommandLine(array(
            'name' => "php build.php",
            'description' => "Run the build script.",
        ));

        $parser->addArgument('actions', array(
            'description' => "Actions to execute.",
            'multiple' => true,
            'optional' => false,
        ));

        $parser->addOption('environment', array(
            'short_name'  => '-e',
            'long_name'   => '--environment',
            'description' => "Environments to use for Create action. Example: '-e developpement'. ",
            'default'     => 'default',
        ));

        $this->result = $parser->parse();

        // Environment
        $this->environment = $this->result->options['environment'];
        if (($this->environment != 'default') && !in_array($this->environment, $this->acceptedEnvironments)) {
            throw new Core_Exception_InvalidArgument(
                'Possible values for -e are ' . implode(',', $this->acceptedEnvironments)
            );
        }

        if ($this->environment != 'default') {
            define('APPLICATION_ENV', $this->environment);
        }
    }

    /**
     * Run all the actions in the build directory
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function run()
    {
        $this->environment = APPLICATION_ENV;

        // Available actions.
        $availableActions = $this->getActions();

        // Actions.
        $actions = [];
        foreach ($this->result->args['actions'] as $actionName) {
            if (! isset($availableActions[$actionName])) {
                $actionChoice = implode(', ', array_keys($availableActions));
                throw new Core_Exception_InvalidArgument(
                    "Action '$actionName' doesn't exist. "
                    ."Possible values for the actions are $actionChoice."
                );
            }
            $actions[$actionName] = $availableActions[$actionName];
        }

        // Run the actions.
        /* @var $action Core_Script_Action */
        foreach ($actions as $actionName => $action) {
            // Action.
            $action->dynamicEnvironments = [$this->environment];
            echo 'Inventory > '.$actionName.PHP_EOL;
            $action->run();
        }
    }

    /**
     * Returns the actions that exists in the build/ directory of the package
     *
     * @throws Exception
     * @return array(actionName => Core_Script_Action)
     */
    private function getActions()
    {
        $actions = array();
        $basePath = PACKAGE_PATH . '/scripts/build';
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
                $classname = 'Inventory_'.ucfirst($file);
                // Check if class exists
                if (! class_exists($classname)) {
                    throw new Exception(
                        "The script file '$scriptFilename' exists, but the class '$classname' was not found inside it"
                    );
                }
                // Load the action class.
                $instance = new $classname();
                $instance->dynamicPackage = 'Inventory';
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
