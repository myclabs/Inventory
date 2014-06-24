<?php

namespace Script\Jobs\Orga;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Orga_Model_Member;

define('RUN', false);
require_once __DIR__ . '/../../../application/init.php';

/**
 * Scripts re-générant les parties céllulaires des exports.
 */
class RebuildPosition
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function run()
    {
        echo "\nRebuilding members positions :";

        /** @var Orga_Model_Member $member */
        foreach (Orga_Model_Member::loadList() as $member) {
            if (($member->getPosition() == null) || ($member->getPosition() == 0)) {
                $member->setPosition();
                echo "\n\t\"" . $member->getTag() . "\" set to position ".$member->getPosition() . ".";
            }
            $member->save();
        }

        echo "\nFlush !\n";
        $this->entityManager->flush();
    }

}

/** @var ContainerInterface $container */
$container = \Core\ContainerSingleton::getContainer();

$rebuildExports = $container->get(RebuildPosition::class);
$rebuildExports->run();
