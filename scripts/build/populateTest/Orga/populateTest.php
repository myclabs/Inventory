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
        $organization = $this->createOrganization('label');

        // Création des axes.
        // Params : Organization, ref, label
        // OptionalParams : Axis parent=null
        $axis1 = $this->createAxis($organization, 'ref1', 'Label 1');
        $axis11 = $this->createAxis($organization, 'ref11', 'Label 11', $axis1);
        $axis2 = $this->createAxis($organization, 'ref11', 'Label 11', $axis1);

        // Création des membres.
        // Params : Axis, ref, label
        // OptionalParams : [Member] parents=[]
        $member11a = $this->createMember($axis11, 'ref11a', 'Label 11 A');
        $member11b = $this->createMember($axis11, 'ref11b', 'Label 11 B');
        $member1a = $this->createMember($axis1, 'ref1a', 'Label 1 A', [$member11a]);
        $member1b = $this->createMember($axis1, 'ref1b', 'Label 1 B', [$member11b]);
        $member2a = $this->createMember($axis2, 'ref2a', 'Label 2 A');

        // Création des granularité.
        // Params : Organization, [Axis], navigable
        // OptionalParams : orgaTab=false, aCL=false, aFTab=false, dWCubes=false, genericAction=false, contextAction=false, inputDocs=false
        $granularityGlobal = $this->createGranularity($organization, [], true, true);
        $granularity11 = $this->createGranularity($organization, [$axis11], false);
        $granularity12 = $this->createGranularity($organization, [$axis1, $axis2], true, false, false, false, true);


        $entityManager->flush();

        echo "\t\tOrganzation created".PHP_EOL;
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
        $orgaTab=false, $aCL=false, $aFTab=false, $dWCubes=false, $genericAction=false, $contextAction=false, $inputDocs=false)
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

}
