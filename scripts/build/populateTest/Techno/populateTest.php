<?php
/**
 * @package Techno
 */

require_once __DIR__ . '/../../populate/Techno/populate.php';

/**
 * Remplissage de la base de données avec des données de test
 * @package Techno
 */
class Techno_PopulateTest extends Techno_Populate
{

    /**
     * {@inheritdoc}
     */
    public function runEnvironment($environment)
    {
        $entityManagers = Zend_Registry::get('EntityManagers');
        /** @var $entityManager \Doctrine\ORM\EntityManager */
        $entityManager = $entityManagers['default'];


        // Création des catégories.
        // Params : ref
        // OptionalParams : Category parent=null
        $category_contenant_sous_categorie = $this->createCategory('Catégorie contenant une sous-catégorie');
        $category_sous_categorie = $this->createCategory('Sous-catégorie', $category_contenant_sous_categorie);
        $category_contenant_famille = $this->createCategory('Catégorie contenant une famille');
        $category_vide = $this->createCategory('Catégorie vide');

        // Création des familles (Coef ou Process).
        // Params : Category, ref, label, refBaseUnit, refUnit
        // $family_1 = $this->createFamilyCoef($category_contenant_famille, 'ref1', 'Label 1', 'm', 'km');
        $family_combustion_combustible_masse = $this->createFamilyProcess($category_contenant_famille,
            'combustion_combustible_unite_masse', 'Combustion de combustible, mesuré en unité de masse', 'kg', 'kg');


        $entityManager->flush();


        // Création des dimensions.
        // Params : Family, refKeyword, refKeywordMembers[]
        $this->createVerticalDimension($family_combustion_combustible_masse, 'combustible', ['charbon', 'gaz_naturel']);
        $this->createHorizontalDimension($family_combustion_combustible_masse, 'processus', ['amont_combustion', 'combustion']);

        // Création des paramètres.
        // Params : Family, refKeywordMembers[], value
        // OptionalParams : uncertainty=0
        $this->createParameter($family_combustion_combustible_masse, ['charbon', 'amont_combustion'], 18);
        $this->createParameter($family_combustion_combustible_masse, ['charbon', 'combustion'], 25, 20);


        $entityManager->flush();

        echo "\t\tTechno created".PHP_EOL;
    }

}
