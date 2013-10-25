<?php
use User\Domain\ACL\ACLFilterService;

/**
 * @package User
 */

require_once 'populateAcl.php';
require_once 'populateUser.php';

/**
 * @package User
 */
class User_Populate extends Core_Script_Populate
{

    /**
     * {@inheritdoc}
     */
    public function populateEnvironment($environment)
    {
        /** @var DI\Container $container */
        $container = Zend_Registry::get('container');
        /** @var $aclFilterService ACLFilterService */
        $aclFilterService = $container->get(ACLFilterService::class);

        // Filtre des ACL
        $aclFilterService->enabled = false;

        $aclScripts = new User_PopulateAcl();
        $aclScripts->runEnvironment($environment);

        $usersScripts = new User_PopulateUser();
        $usersScripts->runEnvironment($environment);

        // Filtre des ACL
        $aclFilterService->enabled = true;
        $aclFilterService->generate();

        echo "\t\tUsers ($environment) : OK".PHP_EOL;
    }

}
