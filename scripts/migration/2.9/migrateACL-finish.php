<?php

use Orga\Model\ACL\Role\CellAdminRole;
use Orga\Model\ACL\Role\CellContributorRole;
use Orga\Model\ACL\Role\CellObserverRole;
use Orga\Model\ACL\Role\OrganizationAdminRole;
use User\Domain\ACL\Resource\NamedResource;
use User\Domain\ACL\Role\AdminRole;
use User\Domain\ACL\Role\UserRole;
use User\Domain\User;

// Ressource "Repository"
$repository = new NamedResource('repository');
$repository->save();
// Ressource abstraite "tous les utilisateurs"
$allUsers = new NamedResource(User::class);
$allUsers->save();
// Ressource abstraite "toutes les organisations"
$allOrganizations = new NamedResource(Orga_Model_Organization::class);
$allOrganizations->save();

$em->flush();
$em->clear();


// User
foreach (User::loadList() as $user) {
    /** @var User $user */
    echo "Adding UserRole to {$user->getEmail()}" . PHP_EOL;
    $user->addRole(new UserRole($user));
}
echo PHP_EOL;

$em->flush();
$em->clear();

// Administrator
foreach ($adminRoles as list($idUser)) {
    $user = User::load($idUser);
    echo "Adding AdminRole to {$user->getEmail()}" . PHP_EOL;
    $user->addRole(new AdminRole($user));

    $em->flush();
    $em->clear();

    unset($user);
    unset($cell);
}
echo PHP_EOL;

// Organization admin
foreach ($organizationAdminRoles as list($idUser, $idOrganization)) {
    $user = User::load($idUser);
    $organization = Orga_Model_Organization::load($idOrganization);
    echo "Adding OrganizationAdminRole to {$user->getEmail()} for organization $idOrganization" . PHP_EOL;
    $user->addRole(new OrganizationAdminRole($user, $organization));

    $em->flush();
    $em->clear();

    unset($user);
    unset($cell);
}
echo PHP_EOL;

// Cell admin
foreach ($cellAdminRoles as list($idUser, $idCell)) {
    $user = User::load($idUser);
    $cell = Orga_Model_Cell::load($idCell);
    echo "Adding CellAdminRole to {$user->getEmail()} for cell $idCell" . PHP_EOL;
    $user->addRole(new CellAdminRole($user, $cell));

    $em->flush();
    $em->clear();

    unset($user);
    unset($cell);
}
echo PHP_EOL;

// Cell contributor
foreach ($cellContributorRoles as list($idUser, $idCell)) {
    $user = User::load($idUser);
    $cell = Orga_Model_Cell::load($idCell);
    echo "Adding CellContributorRole to {$user->getEmail()} for cell $idCell" . PHP_EOL;
    $user->addRole(new CellContributorRole($user, $cell));

    $em->flush();
    $em->clear();

    unset($user);
    unset($cell);
}
echo PHP_EOL;

// Cell observer
foreach ($cellObserverRoles as list($idUser, $idCell)) {
    $user = User::load($idUser);
    $cell = Orga_Model_Cell::load($idCell);
    echo "Adding CellObserverRole to {$user->getEmail()} for cell $idCell" . PHP_EOL;
    $user->addRole(new CellObserverRole($user, $cell));

    $em->flush();
    $em->clear();

    unset($user);
    unset($cell);
}
echo PHP_EOL;
