<?php

namespace Orga\Application\Service\Workspace;

use Core_Locale;
use DateTime;
use Core\ContainerSingleton;
use Doctrine\ORM\EntityManager;
use Exception;
use MyCLabs\ACL\ACL;
use User\Domain\UserService;
use User\Domain\User;
use Orga\Domain\Workspace;
use Orga\Domain\Member;
use Orga\Domain\ACL\CellManagerRole;

/**
 * Service gérant la démo publique et gratuite.
 */
class PublicDemoService
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var ACL
     */
    private $acl;


    /**
     * @param EntityManager $entityManager
     * @param UserService $userService
     * @param OrgaACLManager $orgaACLManager
     */
    public function __construct(
        EntityManager $entityManager,
        UserService $userService,
        ACL $acl
    ) {
        $this->entityManager = $entityManager;
        $this->userService = $userService;
        $this->acl = $acl;
    }

    /**
     * @param $email
     * @param $password
     */
    public function createUserToIndividualDemo($email, $password)
    {
        $this->createUserToDemo('individual', $email, $password);
    }

    /**
     * @param $email
     * @param $password
     */
    public function createUserToCollectivityDemo($email, $password)
    {
        $this->createUserToDemo('collectivity', $email, $password);
    }

    /**
     * @param $email
     * @param $password
     */
    public function createUserToSMEsDemo($email, $password)
    {
        $this->createUserToDemo('smes', $email, $password);
    }

    /**
     * @param string $demo
     * @param string $email
     * @param string $password
     */
    protected function createUserToDemo($demo, $email, $password)
    {
        $container = ContainerSingleton::getContainer();

        try {
            $this->entityManager->beginTransaction();

            /** @var Workspace $workspace */
            $workspace = Workspace::load($container->get('feature.workspace.' . $demo . '.register'));
            $userAxis = $workspace->getAxisByRef($container->get('feature.workspace.' . $demo . '.userAxis.ref'));
            //Création du membre de l'utilisateur.
            $userMember = new Member($userAxis, sha1($email . ' ' . time()));
            $userMember->getLabel()->set(
                $container->get('feature.workspace.' . $demo . '.label.fr') . ' ' .
                '(' . Core_Locale::load('fr')->formatDateTime(new DateTime()) . ')',
                'fr'
            );
            $userMember->getLabel()->set(
                $container->get('feature.workspace.' . $demo . '.label.en') . ' ' .
                '(' . Core_Locale::load('en')->formatDateTime(new DateTime()) . ')',
                'en'
            );
            $userCell = $workspace->getGranularityByRef($userAxis->getRef())->getCellByMembers([$userMember]);

            $this->entityManager->flush();

            // Création de l'utilisateur.
            $user = $this->userService->createUser($email, $password);
            $user->initTutorials();
            $this->entityManager->flush();

            $role = new CellManagerRole($user, $userCell);
            $this->acl->grant($user, $role);

            $this->entityManager->flush();
            $this->entityManager->commit();

            // Envoi d'un mail à la fin de la création.
            $this->userService->sendEmail(
                $user,
                __('Orga', 'publicdemo', 'subjectAccess_' . $demo),
                __('Orga', 'publicdemo', 'bodyAccess_' . $demo )
            );
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}
