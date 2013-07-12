<?php
/**
 * @package Orga
 */


/**
 * Remplissage de la base de données avec des données de test
 * @package Orga
 */
class Orga_PopulateTest extends Core_Script_Action
{
    /**
     * {@inheritdoc}
     */
    public function runEnvironment($environment)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];


        // Création d'une organisation.
        // Param : label
        $organization = $this->createOrganization('Organisation avec données');

        // Création des axes.
        // Params : Organization, ref, label
        // OptionalParams : Axis parent=null
        $axis_annee = $this->createAxis($organization, 'annee', 'Année');
        $axis_site = $this->createAxis($organization, 'site', 'Site');
        $axis_pays = $this->createAxis($organization, 'pays', 'Pays', $axis_site);

        // Création des membres.
        // Params : Axis, ref, label
        // OptionalParams : [Member] parents=[]
        $member_annee_2013 = $this->createMember($axis_annee, '2013', '2013');
        $member_pays_france = $this->createMember($axis_pays, 'france', 'France');
        $member_site_annecy = $this->createMember($axis_site, 'annecy', 'Annecy', [$member_pays_france]);
        $member_site_relie_aucun_pays = $this->createMember($axis_site, 'site_relie_aucun_pays', 'Site relié à aucun pays');

        // Création des granularités.
        // Params : Organization, axes[Axis], navigable
        // OptionalParams : orgaTab=false, aCL=true, aFTab=false, dWCubes=false, genericAction=false, contextAction=false, inputDocs=false
        $granularityGlobal = $this->createGranularity($organization, [],                                                        true,  true,  true,  true,   true,  false, false, false);
        $granularity_site = $this->createGranularity($organization, [$axis_site],                                               true,  false, true,  false,  true,  false, false, true );

        // Granularité des inventaires
        // $organization->setGranularityForInventoryStatus($granularity_annee_zone_marque);

        // Granularités de saisie
        // $granularity_annee_site_categorie->setInputConfigGranularity($granularity_annee_categorie);

        // Création des utilisateurs orga.
        // Params : email
        // $this->createUser('administrateur.organisation@toto.com');
        $entityManager->flush();


        // Ajout d'un role sun une organisation à un utilisateur existant.
        // Params : email, Organization
        $this->addOrganizationAdministrator('admin', $organization);

        // Ajout d'un role sur une cellule à un utilisateur existant.
        // Params : email, Granularity, [Member]

        // La zone-marque pour laquelle les droits sont configurés est "Europe | Marque A".
        // $this->addCellAdministrator('administrateur.zone-marque@toto.com', $granularity_zone_marque, [$member_zone_europe, $member_marque_marque_a]);

        $entityManager->flush();

        echo "\t\tOrganization created".PHP_EOL;
    }

    /**
     * @param string $label
     * @return Orga_Model_Organization
     */
    protected function createOrganization($label)
    {
        $organization = new Orga_Model_Organization();
        $organization->setLabel($label);
        $organization->save();
        return $organization;
    }

    /**
     * @param Orga_Model_Organization $organization
     * @param string $ref
     * @param string $label
     * @param Orga_Model_Axis $narrower
     * @return Orga_Model_Axis
     */
    protected function createAxis(Orga_Model_Organization $organization, $ref, $label, Orga_Model_Axis $narrower=null)
    {
        $axis = new Orga_Model_Axis($organization);
        $axis->setRef($ref);
        $axis->setLabel($label);
        if ($narrower !== null) {
            $axis->setDirectNarrower($narrower);
        }
        $axis->save();
        return $axis;
    }

    /**
     * @param Orga_Model_Axis $axis
     * @param string $ref
     * @param string $label
     * @param array $parents
     * @return Orga_Model_Member
     */
    protected function createMember(Orga_Model_Axis $axis, $ref, $label, array $parents=[])
    {
        $member = new Orga_Model_Member($axis);
        $member->setRef($ref);
        $member->setLabel($label);
        foreach ($parents as $directParent)
        {
            $member->addDirectParent($directParent);
        }
        $member->save();
        return $member;
    }

    /**
     * @param Orga_Model_Organization $organization
     * @param array $axes
     * @param bool $navigable
     * @param bool $orgaTab
     * @param bool $aCL
     * @param bool $aFTab
     * @param bool $dWCubes
     * @param bool $genericAction
     * @param bool $contextAction
     * @param bool $inputDocs
     * @return Orga_Model_Granularity
     */
    protected function createGranularity(Orga_Model_Organization $organization, array $axes=[], $navigable,
        $orgaTab=false, $aCL=true, $aFTab=false, $dWCubes=false, $genericAction=false, $contextAction=false, $inputDocs=false)
    {
        $granularity = new Orga_Model_Granularity($organization, $axes);
        $granularity->setNavigability($navigable);
        $granularity->setCellsWithOrgaTab($orgaTab);
        $granularity->setCellsWithACL($aCL);
        $granularity->setCellsWithAFConfigTab($aFTab);
        $granularity->setCellsGenerateDWCubes($dWCubes);
        $granularity->setCellsWithSocialGenericActions($genericAction);
        $granularity->setCellsWithSocialContextActions($contextAction);
        $granularity->setCellsWithInputDocuments($inputDocs);
        $granularity->save();
        return $granularity;
    }

    protected function createUser($email)
    {
        $user = new User_Model_User();
        $user->setEmail($email);
        $user->setPassword($email);
        $user->save();
    }

    /**
     * @param $email
     * @param Orga_Model_Organization $organization
     */
    protected function addOrganizationAdministrator($email, Orga_Model_Organization $organization)
    {
        $user = User_Model_User::loadByEmail($email);
        Orga_Service_ACLManager::getInstance()->addOrganizationAdministrator($organization, $user);
    }

    /**
     * @param $email
     * @param Orga_Model_Granularity $granularity
     * @param array $members
     */
    protected function addCellAdministrator($email, Orga_Model_Granularity $granularity, array $members)
    {
        $this->addUserToCell('administrator', $email, $granularity, $members);
    }

    /**
     * @param $email
     * @param Orga_Model_Granularity $granularity
     * @param array $members
     */
    protected function addCellContributor($email, Orga_Model_Granularity $granularity, array $members)
    {
        $this->addUserToCell('contributor', $email, $granularity, $members);
    }

    /**
     * @param $email
     * @param Orga_Model_Granularity $granularity
     * @param array $members
     */
    protected function addCellObserver($email, Orga_Model_Granularity $granularity, array $members)
    {
        $this->addUserToCell('observer', $email, $granularity, $members);
    }

    /**
     * @param $role
     * @param $email
     * @param Orga_Model_Granularity $granularity
     * @param array $members
     */
    protected function addUserToCell($role, $email, Orga_Model_Granularity $granularity, array $members)
    {
        $cell = $granularity->getCellByMembers($members);

        $user = User_Model_User::loadByEmail($email);
        $user->addRole(User_Model_Role::loadByRef('cell'.ucfirst(strtolower($role)).'_'.$cell->getId()));
    }

}
