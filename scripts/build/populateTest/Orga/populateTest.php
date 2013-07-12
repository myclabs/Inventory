<?php
/**
 * @package Orga
 */

require_once __DIR__ . '/../../populate/Orga/populate.php';

/**
 * Remplissage de la base de données avec des données de test
 * @package Orga
 */
class Orga_PopulateTest extends Orga_Populate
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
        $organization_vide = $this->createOrganization('Organisation vide');

        // Création des axes.
        // Params : Organization, ref, label
        // OptionalParams : Axis parent=null
        $axis_annee = $this->createAxis($organization, 'annee', 'Année');
        $axis_site = $this->createAxis($organization, 'site', 'Site');
        $axis_pays = $this->createAxis($organization, 'pays', 'Pays', $axis_site);
        $axis_zone = $this->createAxis($organization, 'zone', 'Zone', $axis_pays);
        $axis_marque = $this->createAxis($organization, 'marque', 'Marque', $axis_site);
        $axis_categorie = $this->createAxis($organization, 'categorie', 'Catégorie');
        $axis_vide = $this->createAxis($organization, 'axe_vide', 'Axe vide');

        // Création des membres.
        // Params : Axis, ref, label
        // OptionalParams : [Member] parents=[]
        $member_annee_2013 = $this->createMember($axis_annee, '2013', '2013');
        $member_zone_europe = $this->createMember($axis_zone, 'europe', 'Europe');
        $member_pays_france = $this->createMember($axis_pays, 'france', 'France', [$member_zone_europe]);
        $member_marque_marque_a = $this->createMember($axis_marque, 'marque_a', 'Marque A');
        $member_marque_marque_sans_site = $this->createMember($axis_marque, 'marque_sans_site', 'Marque sans site');
        $member_site_annecy = $this->createMember($axis_site, 'annecy', 'Annecy', [$member_pays_france, $member_marque_marque_a]);
        $member_site_chambery = $this->createMember($axis_site, 'chambery', 'Chambéry', [$member_pays_france, $member_marque_marque_a]);
        $member_site_relie_aucun_pays = $this->createMember($axis_site, 'site_relie_aucun_pays', 'Site relié à aucun pays', [$member_marque_marque_a]);
        $member_categorie = $this->createMember($axis_categorie, 'energie', 'Énergie');

        // Création des granularités.
        // Params : Organization, axes[Axis], navigable
        // OptionalParams : orgaTab=false, aCL=true, aFTab=false, dWCubes=false, genericAction=false, contextAction=false, inputDocs=false
        $granularityGlobal = $this->createGranularity($organization, [],                                                        true,  true,  true,  true,   true,  false, false, false);
        $granularity_zone_marque = $this->createGranularity($organization, [$axis_zone, $axis_marque],                          true,  true,  true,  false,  true,  false, false, false);
        $granularity_site = $this->createGranularity($organization, [$axis_site],                                               true,  false, true,  false,  true,  false, false, true );
        $granularity_annee = $this->createGranularity($organization, [$axis_annee],                                             false, false, false, false,  false, false, false, false);
        $granularity_annee_categorie = $this->createGranularity($organization, [$axis_annee, $axis_categorie],                  false, false, false, false,  false, false, false, false);
        $granularity_annee_zone_marque = $this->createGranularity($organization, [$axis_annee, $axis_zone, $axis_marque],       false, false, false, false,  false, false, false, false);
        $granularity_annee_site = $this->createGranularity($organization, [$axis_annee, $axis_site],                            false, false, false, false,  false, false, false, false);
        $granularity_annee_site_categorie = $this->createGranularity($organization, [$axis_annee, $axis_site, $axis_categorie], false, false, false, false,  false, false, false, false);

        // Granularité des inventaires
        $organization->setGranularityForInventoryStatus($granularity_annee_zone_marque);

        // Granularités de saisie
        $granularity_annee_site_categorie->setInputConfigGranularity($granularity_annee_categorie);

        // Création des utilisateurs orga.
        // Params : email
        $this->createUser('administrateur.organisation@toto.com');
        $this->createUser('administrateur.global@toto.com');
        $this->createUser('contributeur.global@toto.com');
        $this->createUser('observateur.global@toto.com');
        $this->createUser('administrateur.zone-marque@toto.com');
        $this->createUser('contributeur.zone-marque@toto.com');
        $this->createUser('observateur.zone-marque@toto.com');
        $this->createUser('administrateur.site@toto.com');
        $this->createUser('contributeur.site@toto.com');
        $this->createUser('observateur.site@toto.com');
        $this->createUser('utilisateur.connecte@toto.com');
        $entityManager->flush();


        // Ajout d'un role sun une organisation à un utilisateur existant.
        // Params : email, Organization
        $this->addOrganizationAdministrator('admin', $organization);
        $this->addOrganizationAdministrator('administrateur.organisation@toto.com', $organization);
        // Ajout d'un role sur une cellule à un utilisateur existant.
        // Params : email, Granularity, [Member]

        // La zone-marque pour laquelle les droits sont configurés est "Europe | Marque A".
        $this->addCellAdministrator('administrateur.zone-marque@toto.com', $granularity_zone_marque, [$member_zone_europe, $member_marque_marque_a]);
        $this->addCellContributor('contributeur.zone-marque@toto.com', $granularity_zone_marque, [$member_zone_europe, $member_marque_marque_a]);
        $this->addCellObserver('observateur.zone-marque@toto.com', $granularity_zone_marque, [$member_zone_europe, $member_marque_marque_a]);

        // Les sites pour lesquels les droits sont configurés sont "Annecy" et "Chambéry" (deux sites à chaque fois).
        $this->addCellAdministrator('administrateur.site@toto.com', $granularity_site, [$member_site_annecy]);
        $this->addCellContributor('contributeur.site@toto.com', $granularity_site, [$member_site_annecy]);
        $this->addCellObserver('observateur.site@toto.com', $granularity_site, [$member_site_annecy]);
        $this->addCellAdministrator('administrateur.site@toto.com', $granularity_site, [$member_site_chambery]);
        $this->addCellContributor('contributeur.site@toto.com', $granularity_site, [$member_site_chambery]);
        $this->addCellObserver('observateur.site@toto.com', $granularity_site, [$member_site_chambery]);

        $entityManager->flush();

        echo "\t\tOrganization created".PHP_EOL;
    }

}
