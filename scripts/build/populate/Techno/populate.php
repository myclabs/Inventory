<?php

use Keyword\Application\Service\KeywordService;
use Keyword\Domain\Keyword;
use Techno\Domain\Element\CoeffElement;
use Techno\Domain\Element\ProcessElement;
use Techno\Domain\Family\Family;
use Techno\Domain\Family\CoeffFamily;
use Techno\Domain\Family\Dimension;
use Techno\Domain\Family\Member;
use Techno\Domain\Family\ProcessFamily;
use Techno\Domain\Meaning;
use Techno\Domain\Category;

/**
 * Remplissage de la base de données avec des données de test
 * @package Techno
 */
class Techno_Populate extends Core_Script_Action
{

    private $meanings = [];

    /**
     * @var \Keyword\Application\Service\KeywordService
     */
    protected $keywordService;


    function __construct()
    {
        /** @var DI\Container $container */
        $container = Zend_Registry::get('container');
        $this->keywordService = $container->get(KeywordService::class);
    }

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
     * @param Category $parent
     * @return Category
     */
    protected function createCategory($label, Category $parent=null)
    {
        $category = new Category($label);
        if ($parent !== null) {
            $category->setParentCategory($parent);
        }
        $category->save();
        return $category;
    }

    /**
     * @param Category $category
     * @param $ref
     * @param $label
     * @param $refBaseUnit
     * @param $refUnit
     * @param $documentation
     * @return Family
     */
    protected function createFamilyProcess(Category $category, $ref, $label, $refBaseUnit, $refUnit,
        $documentation='')
    {
        $family = new ProcessFamily();
        return $this->createFamily($family, $category, $ref, $label, $refUnit, $refBaseUnit, $documentation);
    }

    /**
     * @param Category $category
     * @param $ref
     * @param $label
     * @param $refBaseUnit
     * @param $refUnit
     * @param $documentation
     * @return Family
     */
    protected function createFamilyCoef(Category $category, $ref, $label, $refBaseUnit, $refUnit,
        $documentation='')
    {
        $family = new CoeffFamily();
        return $this->createFamily($family, $category, $ref, $label, $refUnit, $refBaseUnit, $documentation);
    }

    /**
     * @param Family $family
     * @param Category $category
     * @param $ref
     * @param $label
     * @param $refBaseUnit
     * @param $refUnit
     * @param $documentation
     * @return Family
     */
    protected function createFamily(Family $family, Category $category, $ref, $label, $refBaseUnit, $refUnit,
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
     * @param Family $family
     * @param string $refKeyword
     * @param string[] $keywordMembers
     */
    protected function createHorizontalDimension(Family $family, $refKeyword, array $keywordMembers)
    {
        $this->createDimension($family, $refKeyword, Dimension::ORIENTATION_HORIZONTAL, $keywordMembers);
    }

    /**
     * @param Family $family
     * @param string $refKeyword
     * @param string[] $keywordMembers
     */
    protected function createVerticalDimension(Family $family, $refKeyword, array $keywordMembers)
    {
        $this->createDimension($family, $refKeyword, Dimension::ORIENTATION_VERTICAL, $keywordMembers);
    }

    /**
     * @param Family $family
     * @param string $refKeyword
     * @param int $orientation
     * @param string[] $keywordMembers
     */
    protected function createDimension(Family $family, $refKeyword, $orientation, array $keywordMembers)
    {
        if (!isset($this->meanings[$refKeyword])) {
            $this->meanings[$refKeyword] = new Meaning();
            $this->meanings[$refKeyword]->setKeyword($this->keywordService->get($refKeyword));
            $this->meanings[$refKeyword]->save();
        }
        $dimension = new Dimension($family, $this->meanings[$refKeyword], $orientation);
        foreach ($keywordMembers as $refKeyword) {
            $member = new Member($dimension, $this->keywordService->get($refKeyword));
            $member->save();
            $dimension->addMember($member);
        }
        $dimension->save();
    }

    protected function createParameter(Family $family, array $refKeywordMembers, $value, $uncertainty=0)
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
        if ($family instanceof ProcessFamily) {
            $element = new ProcessElement();
        } else {
            $element = new CoeffElement();
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
