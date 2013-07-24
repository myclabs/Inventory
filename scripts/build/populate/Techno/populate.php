<?php
/**
 * @package Techno
 */


/**
 * Remplissage de la base de données avec des données de test
 * @package Techno
 */
class Techno_Populate extends Core_Script_Action
{

    private $meanings = [];

    /**
     * {@inheritdoc}
     */
    public function runEnvironment($environment)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];


        // Création des catégories.
        //  + createCategory : -
        // Params : ref
        // OptionalParams : Category parent=null

        // Création des familles (Coef ou Process).
        //  + createFamily : -
        // Params : Category, ref, label, refUnit, refBaseUnit
        // OptionalParams : documentation=''


        $entityManager->flush();


        // Création des dimensions.
        //  + createVerticalDimension : -
        //  + createHorizontalDimension : -
        // Params: Family, refKeyword, refKeywordMembers[]

        // Création des paramètres.
        //  + createParameter : -
        // Params : Family, refKeywordMembers[], value
        // OptionalParams : uncertainty=0


        $entityManager->flush();

        echo "\t\tTechno created".PHP_EOL;
    }

    /**
     * @param string $label
     * @param Techno_Model_Category $parent
     * @return Techno_Model_Category
     */
    protected function createCategory($label, Techno_Model_Category $parent=null)
    {
        $category = new Techno_Model_Category();
        $category->setLabel($label);
        if ($parent !== null) {
            $category->setParentCategory($parent);
        }
        $category->save();
        return $category;
    }

    /**
     * @param Techno_Model_Category $category
     * @param $ref
     * @param $label
     * @param $refBaseUnit
     * @param $refUnit
     * @param $documentation
     * @return Techno_Model_Family
     */
    protected function createFamilyProcess(Techno_Model_Category $category, $ref, $label, $refBaseUnit, $refUnit,
        $documentation='')
    {
        $family = new Techno_Model_Family_Process();
        return $this->createFamily($family, $category, $ref, $label, $refUnit, $refBaseUnit, $documentation);
    }

    /**
     * @param Techno_Model_Category $category
     * @param $ref
     * @param $label
     * @param $refBaseUnit
     * @param $refUnit
     * @param $documentation
     * @return Techno_Model_Family
     */
    protected function createFamilyCoef(Techno_Model_Category $category, $ref, $label, $refBaseUnit, $refUnit,
        $documentation='')
    {
        $family = new Techno_Model_Family_Coeff();
        return $this->createFamily($family, $category, $ref, $label, $refUnit, $refBaseUnit, $documentation);
    }

    /**
     * @param Techno_Model_Family $family
     * @param Techno_Model_Category $category
     * @param $ref
     * @param $label
     * @param $refBaseUnit
     * @param $refUnit
     * @param $documentation
     * @return Techno_Model_Family
     */
    protected function createFamily(Techno_Model_Family $family, Techno_Model_Category $category, $ref, $label, $refBaseUnit, $refUnit,
        $documentation='')
    {
        $family->setCategory($category);
        $family->setRef($ref);
        $family->setLabel($label);
        $family->setBaseUnit(new \Unit\UnitAPI($refBaseUnit));
        $family->setUnit(new \Unit\UnitAPI($refUnit));
        $family->setDocumentation($documentation);
        $family->save();
        return $family;
    }

    /**
     * @param Techno_Model_Family $family
     * @param string $refKeyword
     * @param string[] $keywordMembers
     */
    protected function createHorizontalDimension(Techno_Model_Family $family, $refKeyword, array $keywordMembers)
    {
        $this->createDimension($family, $refKeyword, Techno_Model_Family_Dimension::ORIENTATION_HORIZONTAL, $keywordMembers);
    }

    /**
     * @param Techno_Model_Family $family
     * @param string $refKeyword
     * @param string[] $keywordMembers
     */
    protected function createVerticalDimension(Techno_Model_Family $family, $refKeyword, array $keywordMembers)
    {
        $this->createDimension($family, $refKeyword, Techno_Model_Family_Dimension::ORIENTATION_VERTICAL, $keywordMembers);
    }

    /**
     * @param Techno_Model_Family $family
     * @param string $refKeyword
     * @param int $orientation
     * @param string[] $keywordMembers
     */
    protected function createDimension(Techno_Model_Family $family, $refKeyword, $orientation, array $keywordMembers)
    {
        if (!isset($this->meanings[$refKeyword])) {
            $this->meanings[$refKeyword] = new Techno_Model_Meaning();
            $this->meanings[$refKeyword]->setKeyword(Keyword_Model_Keyword::loadByRef($refKeyword));
            $this->meanings[$refKeyword]->save();
        }
        $dimension = new Techno_Model_Family_Dimension($family, $this->meanings[$refKeyword], $orientation);
        foreach ($keywordMembers as $refKeyword) {
            $member = new Techno_Model_Family_Member($dimension, Keyword_Model_Keyword::loadByRef($refKeyword));
            $member->save();
            $dimension->addMember($member);
        }
        $dimension->save();
    }

    protected function createParameter(Techno_Model_Family $family, array $refKeywordMembers, $value, $uncertainty=0)
    {
        // Récupère la cellule
        $members = [];
        foreach ($family->getDimensions() as $dimension) {
            foreach ($dimension->getMembers() as $member) {
                if (in_array($member->getRef(), $refKeywordMembers)) {
                    $members[] = $member;
                }
            }
        }
        $cell = $family->getCell($members);
        // Vérifie qu'il n'y a pas déjà d'élément choisi
        $chosenElement = $cell->getChosenElement();
        if ($chosenElement !== null) {
            throw new Core_Exception("Un élément est déjà choisi pour ces coordonnées.");
        }
        // Crée un élément vide et l'ajoute à la cellule
        if ($family instanceof Techno_Model_Family_Process) {
            $element = new Techno_Model_Element_Process();
        } else {
            $element = new Techno_Model_Element_Coeff();
        }
        $element->setBaseUnit($family->getBaseUnit());
        $element->setUnit($family->getUnit());

        $calcValue = new Calc_Value($value, $uncertainty);
        $element->setValue($calcValue);

        $element->save();
        $cell->setChosenElement($element);
        $cell->save();
    }

}
