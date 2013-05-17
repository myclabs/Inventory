<?php
/**
 * @package Inventory
 * @subpackage ObserverProvider
 */
/**
 * Classe permettant de construire les groupes d'utilisateurs relatifs aux éléments d'Intégration.
 * @author valentin.claras
 * @package Inventory
 * @subpackage ObserverProvider
 *
 */
class Inventory_SocialManager implements Core_Singleton_Abstract, Core_Observer_Abstract
{
    /**
     * Instance unique de la classe.
     * @var Inventory_ETLData
     */
    private static $_instance = null;


    /**
     * Renvoie l'instance Singleton de la classe.
     *
     * @return Inventory_SocialManager
     */
    public static function getInstance()
    {
        if (self::$_instance == null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Test the effect of an Event.
     * @param string            $event
     * @param Core_Model_Entity $subject
     * @param array                $arguments
     * @return array Array of messages (string)
     */
    public static function testEvent($event, $subject, $arguments=null)
    {
        $socialManager = self::getInstance();

        switch ($event) {
            case Orga_Model_Cell::EVENT_DELETE:
                //@todo Ajouter la suppression des RoleUserGroupMemberProvider pour les nouvelles cellules.
                break;
            case User_Model_User::EVENT_DELETE:
                $socialManager->removeUserFromGroups($subject);
                break;
        }
    }

    /**
     * Used when an Event is fired.
     * @param string            $event
     * @param Core_Model_Entity $subject
     * @param array                $arguments
     * @return array Array of messages (string)
     */
    public static function applyEvent($event, $subject, $arguments=null)
    {
        $socialManager = self::getInstance();

        switch ($event) {
            case Inventory_Model_OrgaRoleUserGroupProvider::EVENT_CREATE:
                $socialManager->createRoleUserGroupMemberProviders($subject);
                break;
            case Inventory_Model_RoleUserGroupMemberProvider::EVENT_CREATE:
                $socialManager->populateUserGroupWithMembers($subject);
                break;
            case Orga_Model_Cell::EVENT_CREATE:
                //@todo Ajouter la création des RoleUserGroupMemberProvider pour les nouvelles cellules.
                break;
            case User_Model_User::EVENT_MODIFY:
                $socialManager->removeUserFromNoLongerOwnedGroups($subject);
                $socialManager->addUserToOwnedGroups($subject);
                break;
        }
    }

    /**
     * Créer les OrgaRoleUserGroupProvider
     * @param Inventory_Model_OrgaRoleUserGroupProvider $orgaRoleUserGroupProvider
     */
    protected function createRoleUserGroupMemberProviders($orgaRoleUserGroupProvider)
    {
        $orgaRoleGranularity = $orgaRoleUserGroupProvider->getOrgaRoleGranularityDataProvider()->getOrgaGranularity();
        $perimeterGranularityDataProvider = $orgaRoleUserGroupProvider->getPerimeterGranularityDataProvider();
        $queryCellsPerimeterGranularity = new Core_Model_Query();
        $queryCellsPerimeterGranularity->filter->addCondition(
                Orga_Model_Cell::FILTER_IDCUBE,
                $perimeterGranularityDataProvider->getOrgaGranularity()->getStructure()->getRootCube()->getKey(),
                Core_Model_Filter::OPERATOR_EQUAL
        );
        $queryCellsPerimeterGranularity->filter->addCondition(
                Orga_Model_Cell::FILTER_IDGRANULARITY,
                $perimeterGranularityDataProvider->getKey(),
                Core_Model_Filter::OPERATOR_EQUAL
        );
        foreach (Orga_Model_Cell::loadList($queryCellsPerimeterGranularity) as $perimeterOrgaCell) {
            $socialUserGroup = new Social_Model_UserGroup();
            $socialUserGroup->setRef($orgaRoleGranularity->getRef()
                                        .'#'.$orgaRoleUserGroupProvider->getRoleType()
                                        .'#'.$perimeterOrgaCell->getKey());
            $labelGroup = 'Tous les '.__('Inventory', 'texts', $orgaRoleUserGroupProvider->getRoleType())
                                .' de '.$orgaRoleGranularity->getLabel();
            if ($perimeterOrgaCell->getGranularity()->getRef() !== 'global') {
                $labelGroup .= ' appartenant à '.$perimeterOrgaCell->getLabelCourt();
            }
            $socialUserGroup->setLabel($labelGroup);
            $socialUserGroup->save();
            $roleUserGroupMemberProvider = new Inventory_Model_RoleUserGroupMemberProvider();
            $roleUserGroupMemberProvider->setSocialGroup($socialUserGroup);
            $childCells = Orga_DatagridConfiguration::getDatagridGenericData(new Core_Model_Query(),
                                                $perimeterOrgaCell->getKey(), $orgaRoleGranularity->getKey());
            foreach ($childCells as $childCell) {
                $childCell = Orga_Model_Cell::load($childCell['index']);
                $role = User_Model_Role::loadByCode($orgaRoleUserGroupProvider->getRoleType().$childCell->getKey());
                $roleUserGroupMemberProvider->addRole($role);
            }
            $roleUserGroupMemberProvider->save();
        }
    }

    /**
     * Ajoute les utilisateurs existants au nouveau group.
     * @param Inventory_Model_RoleUserGroupMemberProvider $roleUserGroupMemberProvider
     */
    protected function populateUserGroupWithMembers($roleUserGroupMemberProvider)
    {
        $socialGroup = $roleUserGroupMemberProvider->getSocialGroup();
        foreach ($roleUserGroupMemberProvider->getRoles() as $role) {
            foreach ($role->getUsers() as $user) {
                if (!$socialGroup->hasUser($user)) {
                    $socialGroup->addUser($user);
                }
            }
        }
        $socialGroup->save();
    }

    /**
     * Ajoute un nouvel utilisateur aux groupes auquels il appartient.
     * @param User_Model_User $user
     */
    protected function addUserToOwnedGroups($user)
    {
        foreach ($user->getRoles() as $userRole) {
            foreach (Inventory_Model_RoleUserGroupMemberProvider::loadListByRole($userRole) as $roleUserGroupMemberProvider) {
                $socialGroup = $roleUserGroupMemberProvider->getSocialGroup();
                if (!($socialGroup->hasUser($user))) {
                    $socialGroup->addUser($user);
                    $socialGroup->save();
                }
            }
        }
    }

    /**
     * Retire l'utilisateur des groupes auquels il n'appartient plus.
     * @param User_Model_User $user
     */
    protected function removeUserFromNoLongerOwnedGroups($user)
    {
        foreach (Inventory_Model_RoleUserGroupMemberProvider::loadList() as $roleUserGroupMemberProvider) {
            $socialGroup = $roleUserGroupMemberProvider->getSocialGroup();
            $intersectRoles = array_intersect_key($roleUserGroupMemberProvider->getRoles(), $user->getRoles());
            if (($socialGroup->hasUser($user)) && (empty($intersectRoles))) {
               $socialGroup->removeUser($user);
               $socialGroup->save();
            }
        }
    }

    /**
     * Retire l'utilisateur des groupes auquels il n'appartient plus.
     * @param User_Model_User $user
     */
    protected function removeUserFromGroups($user)
    {
        foreach (Inventory_Model_RoleUserGroupMemberProvider::loadList() as $roleUserGroupMemberProvider) {
            $socialGroup = $roleUserGroupMemberProvider->getSocialGroup();
            if (($socialGroup->hasUser($user))
            ) {
               $socialGroup->removeUser($user);
               $socialGroup->save();
            }
        }
    }

}