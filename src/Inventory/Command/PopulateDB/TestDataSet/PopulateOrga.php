<?php

namespace Inventory\Command\PopulateDB\TestDataSet;

use Account\Domain\Account;
use AF\Domain\AF;
use AF\Domain\Component\Select;
use Calc_UnitValue;
use Classification\Domain\ClassificationLibrary;
use DW\Domain\Report;
use Inventory\Command\PopulateDB\Base\AbstractPopulateOrga;
use Orga\Domain\Cell;
use Symfony\Component\Console\Output\OutputInterface;
use Unit\UnitAPI;

/**
 * Remplissage de la base de données avec des données de test
 *
 * @Injectable(lazy=true)
 */
class PopulateOrga extends AbstractPopulateOrga
{
    public function run(OutputInterface $output)
    {
        $output->writeln('  <info>Populating Orga</info>');

        $library = ClassificationLibrary::load(1);

        // Création d'un compte client
        $account = new Account('Pizza Forever Inc.');
        $this->accountRepository->add($account);

        // Création d'un workspace.
        $workspace = $this->createWorkspace($account, 'Workspace avec données');
        $workspace->addContextIndicator($library->getContextIndicatorByRef('general', 'chiffre_affaire'));
        $workspace->addContextIndicator($library->getContextIndicatorByRef('general', 'ges'));
        $workspace->addContextIndicator($library->getContextIndicatorByRef('deplacement', 'ges'));
        $empty_workspace = $this->createWorkspace($account, 'Workspace vide');

        // Création des axes.
        $axis_annee = $this->createAxis($workspace, 'annee', 'Année', null, true);
        $axis_site = $this->createAxis($workspace, 'site', 'Site', null, false);
        $axis_pays = $this->createAxis($workspace, 'pays', 'Pays', $axis_site, false);
        $axis_zone = $this->createAxis($workspace, 'zone', 'Zone', $axis_pays, false);
        $axis_marque = $this->createAxis($workspace, 'marque', 'Marque', $axis_site, false);
        $axis_categorie = $this->createAxis($workspace, 'categorie', 'Catégorie', null, true);
        $axis_vide = $this->createAxis($workspace, 'axe_vide', 'Axe vide', null, false);

        // Granularité du temps.
        $workspace->setTimeAxis($axis_annee);

        // Création des éléments.
        $member_annee_2012 = $this->createMember($axis_annee, '2012', '2012');
        $member_annee_2013 = $this->createMember($axis_annee, '2013', '2013');
        $member_annee_2014 = $this->createMember($axis_annee, '2014', '2014');
        $member_zone_europe = $this->createMember($axis_zone, 'europe', 'Europe');
        $member_pays_france = $this->createMember($axis_pays, 'france', 'France', [$member_zone_europe]);
        $member_marque_marque_a = $this->createMember($axis_marque, 'marque_a', 'Marque A');
        $member_marque_marque_b = $this->createMember($axis_marque, 'marque_b', 'Marque B');
        $member_marque_marque_sans_site = $this->createMember($axis_marque, 'marque_sans_site', 'Marque sans site');
        $member_site_annecy = $this->createMember($axis_site, 'annecy', 'Annecy', [$member_pays_france, $member_marque_marque_a]);
        $member_site_chambery = $this->createMember($axis_site, 'chambery', 'Chambéry', [$member_pays_france, $member_marque_marque_a]);
        $member_site_grenoble = $this->createMember($axis_site, 'grenoble', 'Grenoble', [$member_pays_france, $member_marque_marque_b]);
        $member_categorie_energie = $this->createMember($axis_categorie, 'energie', 'Énergie');
        $member_categorie_test_affichage = $this->createMember($axis_categorie, 'test_affichage', 'Test affichage');
        $member_categorie_forfait_marque = $this->createMember($axis_categorie, 'forfait_marque', 'Forfait marque');

        // Création des granularités.
        $granularityGlobal = $this->createGranularity($workspace, [],                                                        false, false, true,  true);
        $granularity_zone_marque = $this->createGranularity($workspace, [$axis_zone, $axis_marque],                          true,  false, true,  true);
        $granularity_site = $this->createGranularity($workspace, [$axis_site],                                               false, false, true,  true);
        $granularity_annee = $this->createGranularity($workspace, [$axis_annee],                                             false, false, false, false);
        $granularity_annee_categorie = $this->createGranularity($workspace, [$axis_annee, $axis_categorie],                  false, false, false, false);
        $granularity_annee_zone_marque = $this->createGranularity($workspace, [$axis_annee, $axis_zone, $axis_marque],       false, false, false, false);
        // Granularité des collectes
        $workspace->setGranularityForInventoryStatus($granularity_annee_zone_marque);
        // Création des granularités.
        $granularity_annee_site = $this->createGranularity($workspace, [$axis_annee, $axis_site],                            false, true,  false, false);
        $granularity_annee_site_categorie = $this->createGranularity($workspace, [$axis_annee, $axis_site, $axis_categorie], false, true,  false, false);


        // Granularités de saisie
        $granularityGlobal->setInputConfigGranularity($granularityGlobal); // Utile pour tester le bon affichage dans les onglets Saisies et Formulaires
        $granularity_zone_marque->setInputConfigGranularity($granularity_zone_marque); // Utile pour tester la saisie à un niveau plus grossier que celui des collectes et les ordres entre les granularités des onglets "Collectes" et "Saisies"
        $granularity_annee_site->setInputConfigGranularity($granularityGlobal); // Utile pour tester les ordres entre les granularités des onglets "Collectes" et "Saisies"
        $granularity_annee_site_categorie->setInputConfigGranularity($granularity_annee_categorie);

        // Statut des inventaires
        // 2012 ouvert pour Europe marque A
        $this->setInventoryStatus($granularity_annee_zone_marque, [$member_annee_2012, $member_zone_europe, $member_marque_marque_a], Cell::INVENTORY_STATUS_ACTIVE);
        // 2012 clôturé pour Europe marque B
        $this->setInventoryStatus($granularity_annee_zone_marque, [$member_annee_2012, $member_zone_europe, $member_marque_marque_b], Cell::INVENTORY_STATUS_CLOSED);
        // 2013 ouvert pour Europe marque A
        $this->setInventoryStatus($granularity_annee_zone_marque, [$member_annee_2013, $member_zone_europe, $member_marque_marque_a], Cell::INVENTORY_STATUS_ACTIVE);
        // 2013 non lancé pour Europe marque B (par défaut)

        // Sélection des formulaires
        // Données générales pour la cellule globale
        $this->setAFForChildCells($granularityGlobal, [], $granularityGlobal, 'Données générales');
        // Données générales pour Europe marque A et Europe marque B
        $this->setAFForChildCells($granularity_zone_marque, [$member_zone_europe, $member_marque_marque_a], $granularity_zone_marque, 'Données générales');
        $this->setAFForChildCells($granularity_zone_marque, [$member_zone_europe, $member_marque_marque_b], $granularity_zone_marque, 'Données générales');
        // Données générales pour toutes les cellules de granularié "Année | Site"
        $this->setAFForChildCells($granularityGlobal, [], $granularity_annee_site, 'Données générales');
        // Combustion pour toutes les cellules de granularité "Année | Site | Catégorie" incluses dans "2012|énergie"
        $this->setAFForChildCells($granularity_annee_categorie, [$member_annee_2012, $member_categorie_energie], $granularity_annee_site_categorie, 'Combustion de combustible, mesuré en unité de masse');
        // Test affichage
        $this->setAFForChildCells($granularity_annee_categorie, [$member_annee_2012, $member_categorie_test_affichage], $granularity_annee_site_categorie, 'Formulaire avec tout type de champ');
        // Forfait marque
        $this->setAFForChildCells($granularity_annee_categorie, [$member_annee_2012, $member_categorie_forfait_marque], $granularity_annee_site_categorie, 'Forfait émissions en fonction de la marque');
        // Test affichage sous-formulaire répété tout type de champ
        $this->setAFForChildCells($granularity_annee_categorie, [$member_annee_2013, $member_categorie_test_affichage], $granularity_annee_site_categorie, 'Formulaire avec sous-formulaire répété contenant tout type de champ');

        // Renseignement des saisies
        // Cellule globale, saisie terminée
        $this->setInput($granularityGlobal, [], [
            'chiffre_affaire' => new Calc_UnitValue(new UnitAPI('kiloeuro'), 10, 15)
        ], true);
        // Europe marque A, saisie complète
        $this->setInput($granularity_zone_marque, [$member_zone_europe, $member_marque_marque_a], [
            'chiffre_affaire' => new Calc_UnitValue(new UnitAPI('kiloeuro'), 10, 15)
        ], false);
        // Annecy 2012 (inventaire en cours), saisie complète
        $this->setInput($granularity_annee_site, [$member_annee_2012, $member_site_annecy], [
            'chiffre_affaire' => new Calc_UnitValue(new UnitAPI('kiloeuro'), 10, 15)
        ], false);
        // Annecy | 2012 | Test affichage (inventaire en cours), saisie terminée
        $aF_combustion = $this->getAF('Combustion de combustible, mesuré en unité de masse');
        $select = Select::loadByRef('nature_combustible', $aF_combustion);
        $this->setInput($granularity_annee_site_categorie, [$member_annee_2012, $member_site_annecy, $member_categorie_energie], [
            'nature_combustible' => $select->getOptionByRef('charbon'),
            'quantite_combustible' => new Calc_UnitValue(new UnitAPI('t'), 10, 15),
        ], true);
        // Annecy | 2012 | Test affichage (inventaire en cours), saisie incomplète
        $this->setInput($granularity_annee_site_categorie, [$member_annee_2012, $member_site_annecy, $member_categorie_test_affichage], [
            'c_n' => new Calc_UnitValue(new UnitAPI('kg_co2e.m3^-1'), 10, 15),
        ], false);
        // Chambéry | 2012 | Test affichage (inventaire en cours), saisie complète (pour tester affichage historique)
        $aF_tous_types_champs = $this->getAF('Formulaire avec tout type de champ');
        $c_s_s_liste = Select::loadByRef('c_s_s_liste', $aF_tous_types_champs);
        $c_s_s_bouton = Select::loadByRef('c_s_s_bouton', $aF_tous_types_champs);
        $c_s_m_checkbox = Select::loadByRef('c_s_m_checkbox', $aF_tous_types_champs);
        $c_s_m_liste = Select::loadByRef('c_s_m_liste', $aF_tous_types_champs);
        $this->setInput($granularity_annee_site_categorie, [$member_annee_2012, $member_site_chambery, $member_categorie_test_affichage], [
            'c_n' => new Calc_UnitValue(new UnitAPI('kg_co2e.m3^-1'), 10, 15),
            'c_s_s_liste' => $c_s_s_liste->getOptionByRef('opt_1'),
            'c_s_s_bouton' => $c_s_s_bouton->getOptionByRef('opt_1'),
            'c_s_m_checkbox' => [$c_s_m_checkbox->getOptionByRef('opt_1'), $c_s_m_checkbox->getOptionByRef('opt_2')],
            'c_s_m_liste' => [$c_s_m_liste->getOptionByRef('opt_1'), $c_s_m_checkbox->getOptionByRef('opt_2')],
            'c_b' => true,
            'c_t_c' => 'Blabla',
            'c_t_l' => 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut
labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi
ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse
cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa
qui officia deserunt mollit anim id est laborum.',

        ], false);

        // Grenoble 2012 (inventaire clôturé), saisie terminée
        $this->setInput($granularity_annee_site, [$member_annee_2012, $member_site_grenoble], [
            'chiffre_affaire' => new Calc_UnitValue(new UnitAPI('kiloeuro'), 10, 15)
        ], true);
        // Annecy 2013 (inventaire non lancé), saisie complète
        $this->setInput($granularity_annee_site, [$member_annee_2013, $member_site_annecy], [
            'chiffre_affaire' => new Calc_UnitValue(new UnitAPI('kiloeuro'), 10, 15)
        ], false);

        $this->entityManager->flush();

        // Création d'analyses préconfigurées
        $this->createSimpleGranularityReport($granularityGlobal, 'Chiffre d\'affaire, par année', '1_chiffre_affaire', 'o_annee', [], false, Report::CHART_PIE, Report::SORT_CONVENTIONAL);
        $this->createSimpleGranularityReport($granularityGlobal, 'Chiffre d\'affaire 2012, marques A et B, par site', '1_chiffre_affaire', 'o_site', ['o_annee' => ['2012'], 'o_marque' => ['marque_a', 'marque_b']], false, Report::CHART_PIE, Report::SORT_VALUE_DECREASING);
        $this->createSimpleGranularityReport($granularity_site, 'Chiffre d\'affaire, par année', '1_chiffre_affaire', 'o_annee', [], false, Report::CHART_PIE, Report::SORT_CONVENTIONAL);

        // Création des utilisateurs orga.
        $this->createUser('administrateur.workspace@toto.com');
        $this->createUser('administrateur.global@toto.com');
        $this->createUser('coordinateur.global@toto.com');
        $this->createUser('contributeur.global@toto.com');
        $this->createUser('observateur.global@toto.com');
        $this->createUser('administrateur.zone-marque@toto.com');
        $this->createUser('coordinateur.zone-marque@toto.com');
        $this->createUser('contributeur.zone-marque@toto.com');
        $this->createUser('observateur.zone-marque@toto.com');
        $this->createUser('administrateur.site@toto.com');
        $this->createUser('coordinateur.site@toto.com');
        $this->createUser('contributeur.site@toto.com');
        $this->createUser('observateur.site@toto.com');
        $this->createUser('utilisateur.connecte@toto.com');

        // Création utilisateur pour test édition "mon compte" et test édition compte d'un utilisateur.
        $this->createUser('emmanuel.risler.pro@gmail.com');

        $this->entityManager->flush();

        // Ajout d'un role d'administrateur de workspace à un utilisateur existant.
        $this->addWorkspaceAdministrator('administrateur.workspace@toto.com', $workspace);

        // Ajout d'un role sur une cellule à un utilisateur existant.

        // Cellule globale
        $this->addCellAdministrator('administrateur.global@toto.com', $granularityGlobal, []);
        $this->addCellManager('coordinateur.global@toto.com', $granularityGlobal, []);
        $this->addCellContributor('contributeur.global@toto.com', $granularityGlobal, []);
        $this->addCellObserver('observateur.global@toto.com', $granularityGlobal, []);

        // La zone-marque pour laquelle les droits sont configurés est "Europe | Marque A".
        $this->addCellAdministrator('administrateur.zone-marque@toto.com', $granularity_zone_marque, [$member_zone_europe, $member_marque_marque_a]);
        $this->addCellManager('coordinateur.zone-marque@toto.com', $granularity_zone_marque, [$member_zone_europe, $member_marque_marque_a]);
        $this->addCellContributor('contributeur.zone-marque@toto.com', $granularity_zone_marque, [$member_zone_europe, $member_marque_marque_a]);
        $this->addCellObserver('observateur.zone-marque@toto.com', $granularity_zone_marque, [$member_zone_europe, $member_marque_marque_a]);

        // Les sites pour lesquels les droits sont configurés sont "Annecy" et "Chambéry" (deux sites à chaque fois).
        $this->addCellAdministrator('administrateur.site@toto.com', $granularity_site, [$member_site_annecy]);
        $this->addCellManager('coordinateur.site@toto.com', $granularity_site, [$member_site_annecy]);
        $this->addCellContributor('contributeur.site@toto.com', $granularity_site, [$member_site_annecy]);
        $this->addCellObserver('observateur.site@toto.com', $granularity_site, [$member_site_annecy]);
        //
        $this->addCellAdministrator('administrateur.site@toto.com', $granularity_site, [$member_site_chambery]);
        $this->addCellManager('coordinateur.site@toto.com', $granularity_site, [$member_site_chambery]);
        $this->addCellContributor('contributeur.site@toto.com', $granularity_site, [$member_site_chambery]);
        $this->addCellObserver('observateur.site@toto.com', $granularity_site, [$member_site_chambery]);


        $this->entityManager->flush();

        $this->createFreeAppAccount();
    }

    public function createFreeAppAccount()
    {
        // Création d'un compte d'application gratuite
        $account = new Account('Application gratuite');
        $this->accountRepository->add($account);

        /** @var ClassificationLibrary $library */
        $library = ClassificationLibrary::load(1);


        // Création d'un workspace individuel.
        $individualWorkspace = $this->createWorkspace($account, 'Individual free app');
        $individualWorkspace->addContextIndicator($library->getContextIndicatorByRef('general', 'chiffre_affaire'));
        $individualWorkspace->addContextIndicator($library->getContextIndicatorByRef('general', 'ges'));
        $individualWorkspace->addContextIndicator($library->getContextIndicatorByRef('deplacement', 'ges'));

        // Création des axes.
        $yearAxis = $this->createAxis($individualWorkspace, 'year', 'Année', null, true);
        $yearAxis->getLabel()->set('Year', 'en');
        $homeAxis = $this->createAxis($individualWorkspace, 'home', 'Habitation', null, false);
        $homeAxis->getLabel()->set('Home', 'en');

        // Axe du temps.
        $individualWorkspace->setTimeAxis($yearAxis);

        // Création des éléments.
        $year_2013Member = $this->createMember($yearAxis, '2013', '2013');
        $year_2014Member = $this->createMember($yearAxis, '2014', '2014');
        $year_2015Member = $this->createMember($yearAxis, '2015', '2015');
        $year_2016Member = $this->createMember($yearAxis, '2016', '2016');

        // Création des granularités.
        $globalGranularity = $this->createGranularity($individualWorkspace, [],                       false, false, false, false);
        $homeGranularity = $this->createGranularity($individualWorkspace, [$homeAxis],                false, false, true,  true);
        $yearGranularity = $this->createGranularity($individualWorkspace, [$yearAxis],                false, false, false, false);
        $yearHomeGranularity = $this->createGranularity($individualWorkspace, [$yearAxis, $homeAxis], false, false, false, false);

        // Granularité des collectes.
        $individualWorkspace->setGranularityForInventoryStatus($yearGranularity);

        // Granularités de saisie.
        $yearHomeGranularity->setInputConfigGranularity($globalGranularity);

        // Statut des inventaires.
        // 2013 clôturé.
        $this->setInventoryStatus($yearGranularity, [$year_2013Member], Cell::INVENTORY_STATUS_CLOSED);
        // 2014 ouvert.
        $this->setInventoryStatus($yearGranularity, [$year_2014Member], Cell::INVENTORY_STATUS_ACTIVE);
        // 2015 ouvert.
        $this->setInventoryStatus($yearGranularity, [$year_2015Member], Cell::INVENTORY_STATUS_ACTIVE);

        // Sélection des formulaires.
        $this->setAFForChildCells($globalGranularity, [], $yearHomeGranularity, 'Données générales');

        // Flush.
        $this->entityManager->flush();


        // Création d'un workspace PME.
        $smesWorkspace = $this->createWorkspace($account, 'SMEs free app');
        $smesWorkspace->addContextIndicator($library->getContextIndicatorByRef('general', 'chiffre_affaire'));
        $smesWorkspace->addContextIndicator($library->getContextIndicatorByRef('general', 'ges'));
        $smesWorkspace->addContextIndicator($library->getContextIndicatorByRef('deplacement', 'ges'));

        // Création des axes.
        $yearAxis = $this->createAxis($smesWorkspace, 'year', 'Année', null, true);
        $yearAxis->getLabel()->set('Year', 'en');
        $companyAxis = $this->createAxis($smesWorkspace, 'company', 'Entreprise', null, false);
        $companyAxis->getLabel()->set('Company', 'en');
        $categoryAxis = $this->createAxis($smesWorkspace, 'category', 'Catégorie', null, true);
        $categoryAxis->getLabel()->set('Category', 'en');

        // Axe du temps.
        $smesWorkspace->setTimeAxis($yearAxis);

        // Création des éléments.
        $year_2013Member = $this->createMember($yearAxis, '2013', '2013');
        $year_2014Member = $this->createMember($yearAxis, '2014', '2014');
        $year_2015Member = $this->createMember($yearAxis, '2015', '2015');
        $year_2016Member = $this->createMember($yearAxis, '2016', '2016');
        $category_generalMember = $this->createMember($categoryAxis, 'general', 'Général');
        $category_generalMember->getLabel()->set('General', 'en');
        $category_energyMember = $this->createMember($categoryAxis, 'energy', 'Énergie');
        $category_energyMember->getLabel()->set('Energy', 'en');

        // Création des granularités.
        $globalGranularity = $this->createGranularity($smesWorkspace, [],                                                    false, false, false, false);
        $companyGranularity = $this->createGranularity($smesWorkspace, [$companyAxis],                                       false, false, true,  true);
        $yearGranularity = $this->createGranularity($smesWorkspace, [$yearAxis],                                             false, false, false, false);
        $categoryGranularity = $this->createGranularity($smesWorkspace, [$categoryAxis],                                     false, false, false, false);
        $yearCompanyCategoryGranularity = $this->createGranularity($smesWorkspace, [$yearAxis, $companyAxis, $categoryAxis], false, false, false, false);

        // Granularité des collectes.
        $smesWorkspace->setGranularityForInventoryStatus($yearGranularity);

        // Granularités de saisie.
        $yearCompanyCategoryGranularity->setInputConfigGranularity($categoryGranularity);

        // Statut des inventaires.
        // 2013 clôturé.
        $this->setInventoryStatus($yearGranularity, [$year_2013Member], Cell::INVENTORY_STATUS_CLOSED);
        // 2014 ouvert.
        $this->setInventoryStatus($yearGranularity, [$year_2014Member], Cell::INVENTORY_STATUS_ACTIVE);
        // 2015 ouvert.
        $this->setInventoryStatus($yearGranularity, [$year_2015Member], Cell::INVENTORY_STATUS_ACTIVE);

        // Sélection des formulaires.
        $this->setAFForChildCells($categoryGranularity, [$category_generalMember], $yearCompanyCategoryGranularity, 'Données générales');
        $this->setAFForChildCells($categoryGranularity, [$category_energyMember], $yearCompanyCategoryGranularity, 'Combustion de combustible, mesuré en unité de masse');

        // Flush.
        $this->entityManager->flush();
    }
}
