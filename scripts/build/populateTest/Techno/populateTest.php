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


        $categorie_contenant_sous_categorie = $this->createCategory('Catégorie contenant une sous-catégorie');
        $categorie_sous_categorie = $this->createCategory('Sous-catégorie', $categorie_contenant_sous_categorie);
        $categorie_contenant_famille = $this->createCategory('Catégorie contenant une famille');
        $sous_categorie_contenant_famille = $this->createCategory('Sous-catégorie contenant une famille', $categorie_contenant_famille);
        $categorie_vide = $this->createCategory('Catégorie vide');

        // $family_1 = $this->createFamilyCoef($category_contenant_famille, 'ref1', 'Label 1', 'km', 'm', '');

        $family_combustion_combustible_masse = $this->createFamilyCoef($categorie_contenant_famille,
            'combustion_combustible_unite_masse', 'Combustion de combustible, mesuré en unité de masse', 'kg_co2e.t^-1', 'kg_co2e.kg^-1');
        $family_masse_volumique_combustible = $this->createFamilyCoef($categorie_contenant_famille,
            'masse_volumique_combustible', 'Masse volumique de combustible', 't.m3^-1', 'kg.m3^-1');
        $family_forfait_emissions_fonction_marque = $this->createFamilyCoef($categorie_contenant_famille,
            'forfait_emissions_fonction_marque', 'Forfait émissions en fonction de la marque', 't_co2e', 'kg_co2e');
        $family_vide = $this->createFamilyCoef($categorie_contenant_famille,
            'famille_test_vide', 'Famille test vide', 't', 'kg');
        $family_test = $this->createFamilyCoef($sous_categorie_contenant_famille,
            'famille_test_non_vide', 'Famille test non vide', 'kg_co2e.t^-1', 'kg_co2e.kg^-1', 'h1. Documentation de la famille test');

        $entityManager->flush();


        // Combustion de combustible, mesuré en unité de masse
        $this->createVerticalDimension($family_combustion_combustible_masse, 'combustible', ['charbon', 'gaz_naturel']);
        $this->createHorizontalDimension($family_combustion_combustible_masse, 'processus', ['amont_combustion', 'combustion']);

        $this->createParameter($family_combustion_combustible_masse, ['charbon', 'amont_combustion'], 254, 20);
        $this->createParameter($family_combustion_combustible_masse, ['charbon', 'combustion'], 3077, 20);

        // Masse volumique de combustible
        $this->createVerticalDimension($family_masse_volumique_combustible, 'combustible', ['charbon', 'gaz_naturel']);
        $this->createParameter($family_masse_volumique_combustible, ['charbon'], 900, 10);

        // Forfait émissions en fonction de la marque
        $this->createVerticalDimension($family_forfait_emissions_fonction_marque, 'marque', ['marque_a', 'marque_b']);
        $this->createParameter($family_forfait_emissions_fonction_marque, ['marque_a'], 1, 10);
        $this->createParameter($family_forfait_emissions_fonction_marque, ['marque_b'], 2, 10);

        // Famille test
        $this->createVerticalDimension($family_test, 'combustible', ['charbon', 'gaz_naturel']);
        $this->createHorizontalDimension($family_test, 'processus', ['amont_combustion', 'combustion']);
        $this->createParameter($family_test, ['charbon', 'combustion'], 12345.6789, 15.9);
        $this->createParameter($family_test, ['charbon', 'amont_combustion'], 0.1234, 15.9);

        // Famille test de coefficients

        $entityManager->flush();

        echo "\t\tTechno created".PHP_EOL;
    }

}
