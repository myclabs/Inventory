<?php

namespace Inventory\Command\PopulateDB\TestDWDataSet;

use Account\Domain\Account;
use Inventory\Command\PopulateDB\Base\AbstractPopulateOrga;
use Symfony\Component\Console\Output\OutputInterface;

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

        // Création d'un compte client
        $account = new Account('The fantastic pizza factory');
        $this->accountRepository->add($account);

        // Création d'une organisation.
        // Param : label
        $organization = $this->createOrganization($account, 'Workspace avec données');

        // Création des axes.
        // Params : Organization, ref, label
        // OptionalParams : Axis parent=null
        $axis_site = $this->createAxis($organization, 'site', 'Site');
        $axis_pays = $this->createAxis($organization, 'pays', 'Pays', $axis_site);

        // Création des membres.
        // Params : Axis, ref, label
        // OptionalParams : [Member] parents=[]
        $member_pays_france = $this->createMember($axis_pays, 'france', 'France');
        $member_pays_italie = $this->createMember($axis_pays, 'italie', 'Italie');

        // Création des granularités.
        // Params : Organization, axes[Axis], navigable
        // OptionalParams : orgaTab=false, aCL=true, aFTab=false, dWCubes=false, genericAction=false, contextAction=false, inputDocs=false
        $granularityGlobal = $this->createGranularity($organization, [],                                                        true, false, true,  true, true,  true, false, false, false);
        $granularity_site = $this->createGranularity($organization, [$axis_site],                                               true, false, false, true, false, true, false, false, true );

        // Granularité des inventaires
        // $organization->setGranularityForInventoryStatus($granularity_annee_zone_marque);

        // Granularités de saisie
        // $granularity_annee_site_categorie->setInputConfigGranularity($granularity_annee_categorie);

        $this->entityManager->flush();

        // Création des utilisateurs orga.
        // Params : email
        // $this->createUser('administrateur.workspace@toto.com');

        // Ajout d'un role sun une organisation à un utilisateur existant.
        // Params : email, Organization
        $this->addOrganizationAdministrator('admin@myc-sense.com', $organization);

        // Ajout d'un role sur une cellule à un utilisateur existant.
        // Params : email, Granularity, [Member]

        // La zone-marque pour laquelle les droits sont configurés est "Europe | Marque A".
        // $this->addCellAdministrator('administrateur.zone-marque@toto.com', $granularity_zone_marque, [$member_zone_europe, $member_marque_marque_a]);

        $this->entityManager->flush();
    }
}
