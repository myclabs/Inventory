<?php

namespace Inventory\Command\PopulateDB\TestDataSet;

use Account\Domain\Account;
use Classification\Domain\ClassificationLibrary;
use Doctrine\ORM\EntityManager;
use Inventory\Command\PopulateDB\Base\AbstractPopulateClassification;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Remplissage de la base de données avec des données de test
 *
 * Ce service est lazy car on veut injecter "account.myc-sense" après que ça ait été créé.
 *
 * @Injectable(lazy=true)
 */
class PopulateClassification extends AbstractPopulateClassification
{
    /**
     * @Inject
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @Inject("account.myc-sense")
     * @var Account
     */
    private $publicAccount;

    public function run(OutputInterface $output)
    {
        $output->writeln('  <info>Populating Classification</info>');

        $library = new ClassificationLibrary($this->publicAccount, 'Classification My C-Sense');
        $library->save();

        // Création des axes.
        $axis_gaz = $this->createAxis($library, 'gaz', 'Gaz');
        $axis_poste_article_75 = $this->createAxis($library, 'poste_article_75', 'Poste article 75');
        $axis_scope = $this->createAxis($library, 'scope', 'Scope', $axis_poste_article_75);
        $axis_type_deplacement = $this->createAxis($library, 'type_deplacement', 'Type de déplacement');
        $axis_axe_vide = $this->createAxis($library, 'axe_vide', 'Axe vide');

        // Création des éléments.
        $member_gaz_co2 = $this->createMember($axis_gaz, 'co2', 'CO2');
        $member_gaz_ch4 = $this->createMember($axis_gaz, 'ch4', 'CH4');

        $member_scope_1 = $this->createMember($axis_scope, '1', '1');
        $member_scope_2 = $this->createMember($axis_scope, '2', '2');
        $member_scope_3 = $this->createMember($axis_scope, '3', '3');

        $member_poste_article_75_source_fixe_combustion = $this->createMember($axis_poste_article_75, 'source_fixe_combustion', '1 - Sources fixes de combustion', [$member_scope_1]);
        $member_poste_article_75_electricite = $this->createMember($axis_poste_article_75, 'element_sans_parent', 'Élément sans parent');

        $member_deplacement = $this->createMember($axis_type_deplacement, 'domicile_travail', 'Domicile - travail');
        $member_deplacement = $this->createMember($axis_type_deplacement, 'professionnel', 'Professionnel');

        // Création des indicateurs.
        $indicator_ges = $this->createIndicator($library, 'ges', 'GES', 't_co2e', 'kg_co2e');
        $indicator_chiffre_affaire = $this->createIndicator($library, 'chiffre_affaire', 'Chiffre d\'affaires', 'kiloeuro', 'euro');
        $indicator_no_context_indicator = $this->createIndicator($library, 'sans_indicateur_contextualise', 'Sans indicateur contextualisé', 't', 't');
        $indicator_related_axes = $this->createIndicator($library, 'axes_relies', 'Axes hiérarchiquement reliés', 't', 't');

        // Création des contextes.
        $context_general = $this->createContext($library, 'general', 'Général');
        $context_deplacement = $this->createContext($library, 'deplacement', 'Déplacements');
        $context_no_context_indicator = $this->createContext($library, 'sans_indicateur_contextualise', 'Sans indicateur contextualisé');


        $this->entityManager->flush();


        // Création des contexte-indicateurs.
        $contextIndicator_ges_general = $this->createContextIndicator($library, $context_general, $indicator_ges, [$axis_gaz, $axis_poste_article_75]);
        $contextIndicator_ges_deplacement = $this->createContextIndicator($library, $context_deplacement, $indicator_ges, [$axis_gaz, $axis_poste_article_75, $axis_type_deplacement]);
        $contextIndicator_chiffre_affaire_general = $this->createContextIndicator($library, $context_general, $indicator_chiffre_affaire);


        $this->entityManager->flush();
    }
}
