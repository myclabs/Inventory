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


        $category_contenant_sous_categorie = $this->createCategory('Catégorie contenant une sous-catégorie');
        $category_sous_categorie = $this->createCategory('Sous-catégorie', $category_contenant_sous_categorie);
        $category_contenant_famille = $this->createCategory('Catégorie contenant une famille');
        $category_vide = $this->createCategory('Catégorie vide');

        // $family_1 = $this->createFamilyCoef($category_contenant_famille, 'ref1', 'Label 1', 'km', 'm', '');

        $family_combustion_combustible_masse = $this->createFamilyProcess($category_contenant_famille,
            'combustion_combustible_unite_masse', 'Combustion de combustible, mesuré en unité de masse', 't', 'kg', 'h1. Documentation de la famille "Combustion…"');
        $family_masse_volumique_combustible = $this->createFamilyCoef($category_contenant_famille,
            'masse_volumique_combustible', 'Masse volumique de combustible', 't.m3^-1', 'kg.m3^-1');
        $family_vide_processus = $this->createFamilyProcess($category_contenant_famille,
            'famille_vide_processus', 'Famille vide de processus', 't', 'kg');
        $family_vide_coefficients = $this->createFamilyCoef($category_contenant_famille,
            'famille_vide_coefficients', 'Famille vide de coefficients', 't', 'kg');
        $family_test_processus = $this->createFamilyProcess($category_contenant_famille,
            'famille_test_processus', 'Famille test de processus', 't', 'kg');
        $family_test_coefficients = $this->createFamilyCoef($category_contenant_famille,
        'famille_test_coefficients', 'Famille test de coefficients', 't', 'kg');

        $entityManager->flush();


        // Combustion de combustible, mesuré en unité de masse
        $this->createVerticalDimension($family_combustion_combustible_masse, 'combustible', ['charbon', 'gaz_naturel']);
        $this->createHorizontalDimension($family_combustion_combustible_masse, 'processus', ['amont_combustion', 'combustion']);

        $this->createParameter($family_combustion_combustible_masse, ['charbon', 'amont_combustion'], 18);
        $this->createParameter($family_combustion_combustible_masse, ['charbon', 'combustion'], 25, 20);

        // Masse volumique de combustible
        $this->createVerticalDimension($family_masse_volumique_combustible, 'combustible', ['charbon', 'gaz_naturel']);
        $this->createParameter($family_masse_volumique_combustible, ['charbon'], 18);

        // Famille test de processus
        $this->createVerticalDimension($family_test_processus, 'combustible', ['charbon', 'gaz_naturel']);
        $this->createHorizontalDimension($family_test_processus, 'processus', ['amont_combustion', 'combustion']);
        $this->createParameter($family_test_processus, ['charbon', 'combustion'], 12345.6789, 15.9);
        $this->createParameter($family_test_processus, ['charbon', 'amont_combustion'], 0.1234, 15.9);

        // Famille test de coefficients

        $entityManager->flush();

        echo "\t\tTechno created".PHP_EOL;
    }

}
