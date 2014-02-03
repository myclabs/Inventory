@dbFull
Feature: Cell administrator feature

  @javascript @readOnly
  Scenario: Global cell administrator login scenario
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'administrateur de la cellule globale
    When I fill in "email" with "administrateur.global@toto.com"
    And I fill in "password" with "administrateur.global@toto.com"
    And I click "connection"
  # On tombe sur la page de la cellule globale
    Then I should see "Vue globale Workspace avec données"
    When I wait 5 seconds
  # Vérification onglets visibles et invisibles
    When I open tab "Paramétrage"
  # Vérification que, sous l'onglet "Paramétrage", l'utilisateur voit uniquement "Éléments" et "Pertinence"
    And I open tab "Éléments"
    And I open tab "Pertinence"
    Then I should not see "Informations générales"
    And I should not see "Axes"
    And I should not see "Niveaux"
    And I should not see "Contrôle"

  @javascript @readOnly
  Scenario: Single cell administrator login scenario
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login
    When I fill in "email" with "administrateur.zone-marque@toto.com"
    And I fill in "password" with "administrateur.zone-marque@toto.com"
    And I click "connection"
  # On tombe sur la page de la cellule
    Then I should see "Europe | Marque A Workspace avec données"
    When I wait 5 seconds
  # Vérification que le libellé "Vue globale" est présent mais non cliquable dans le volet de navigation
  # Voir "Organization navigation scenario"
    And I click element ".fa-plus"
    Then I should see "Vue globale"
    And I should not see a "#navigationParent a:contains('Vue globale')" element
  # Accès à une saisie et à l'historique des valeurs d'un champ (suite à détection bug droits utilisateur)
    When I open collapse "Zone | Marque"
    And I click "Cliquer pour accéder" in the row 1 of the "aFGranularity2Input2" datagrid
    And I click element "#chiffre_affaireHistory .btn"
    Then I should see "Historique des valeurs"
    And I should see a "code:contains('10 k€ ± 15 %')" element

  @javascript @readOnly
  Scenario: Several cells administrator login scenario
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "administrateur.site@toto.com"
    And I fill in "password" with "administrateur.site@toto.com"
    And I click "connection"
  # On tombe sur le datagrid des cellules
    Then I should see the "listCells" datagrid
    And the "listCells" datagrid should contain 2 row
    And the row 1 of the "listCells" datagrid should contain:
      | label  | access         |
      | Annecy | Administrateur |
  # Accès à une des cellules
    When I click "Accéder aux saisies" in the row 1 of the "listCells" datagrid
    Then I should see "Annecy Workspace avec données"
    When I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity5Input8" datagrid
  # Accès à l'onglet "Analyses", vérification que l'utilisateur peut bien voir les analyses préconfigurées
    When I open tab "Analyses"
    Then the row 1 of the "report" datagrid should contain:
      | label                        |
      | Chiffre d'affaire, par année |
    When I click "Cliquer pour accéder" in the row 1 of the "report" datagrid
    And I open tab "Valeurs"
    Then the row 1 of the "reportValues" datagrid should contain:
      | valueAxiso_annee | valueDigital | valueUncertainty |
      | 2012                | 10           | 15               |
    And the row 2 of the "reportValues" datagrid should contain:
      | valueAxiso_annee | valueDigital | valueUncertainty |
      | 2013                | 10           | 15               |

  @javascript
  Scenario: Cell administrator members tab, creation and modification of a member
  #6268, #6300
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "administrateur.zone-marque@toto.com"
    And I fill in "password" with "administrateur.zone-marque@toto.com"
    And I click "connection"
    And I wait 5 seconds
    Then I should see "Europe | Marque A Workspace avec données"
  # Vérification qu'on a bien accès à l'onglet "Paramétrage" et à ses sous-onglets
    When I open tab "Paramétrage"
  # On tombe sur l'onglet "Éléments"
    And I open collapse "Site"
    Then I should see the "listMemberssite" datagrid
  # Ajout d'un élément, saisie correcte (parent renseigné en partie)
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un élément à l'axe « Site »"
    When I fill in "listMemberssite_label_addForm" with "AAA"
    And I fill in "listMemberssite_ref_addForm" with "aaa"
    And I fill in "listMemberssite_broaderpays_addForm" with "france#da39a3ee5e6b4b0d3255bfef95601890afd80709"
    And I fill in "listMemberssite_broadermarque_addForm" with "marque_a#da39a3ee5e6b4b0d3255bfef95601890afd80709"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 1 of the "listMemberssite" datagrid should contain:
      | label | ref | broaderpays | broadermarque |
      | AAA   | aaa | France      | Marque A      |
  # Modification d'un élément
    When I set "Annecy modifiée" for column "label" of row 2 of the "listMemberssite" datagrid with a confirmation message
    And I set "annecy_modifie" for column "ref" of row 2 of the "listMemberssite" datagrid with a confirmation message
    Then the row 2 of the "listMemberssite" datagrid should contain:
      | label           | ref            |
      | Annecy modifiée | annecy_modifie |

  @javascript
  Scenario: Cell administrator members tab, deletion of a member
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "administrateur.zone-marque@toto.com"
    And I fill in "password" with "administrateur.zone-marque@toto.com"
    And I click "connection"
    And I open tab "Paramétrage"
  # Ajout et suppression d'un élément à l'axe "Pays"
    And I open collapse "Pays"
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un élément à l'axe « Pays »"
    When I fill in "listMemberspays_label_addForm" with "AAA"
    And I fill in "listMemberspays_ref_addForm" with "aaa"
    And I fill in "listMemberspays_broaderzone_addForm" with "europe#da39a3ee5e6b4b0d3255bfef95601890afd80709"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the "listMemberspays" datagrid should contain 2 row
    And the row 1 of the "listMemberspays" datagrid should contain:
      | label | ref | broaderzone |
      | AAA   | aaa | Europe      |
    When I click "Supprimer" in the row 1 of the "listMemberspays" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "listMemberspays" datagrid should contain 1 row
  # TODO : Suppression d'un élément entraînant la suppression de cellules associées à des DWs (par exemple un site).


  @javascript
  Scenario: Cell administrator subunits and relevance tabs
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "administrateur.zone-marque@toto.com"
    And I fill in "password" with "administrateur.zone-marque@toto.com"
    And I click "connection"
    Then I should see "Europe | Marque A Workspace avec données"
  # Vérification qu'on a bien accès à l'onglet "Paramétrage" et à ses sous-onglets
    When I open tab "Paramétrage"
  # Accès à l'onglet "Sous-unités"
    # And I open tab "Sous-unités"
    # And I open collapse "Site"
    # Then I should see the "child_c2_g3" datagrid
  # Accès à l'onglet "Pertinence"
    When I open tab "Pertinence"
    And I open collapse "Site"
    Then I should see the "relevant_c2_g3" datagrid
    And the row 1 of the "relevant_c2_g3" datagrid should contain:
      | site   | relevant   |
      | Annecy | Pertinente |
  # Édition de la pertinence : rendre non pertinente une cellule pertinente
    When I set "Non pertinente" for column "relevant" of row 1 of the "relevant_c2_g3" datagrid with a confirmation message
    Then the row 1 of the "relevant_c2_g3" datagrid should contain:
      | site   | relevant       |
      | Annecy | Non pertinente |
  # Édition de la pertinence : rendre pertinente une cellule non pertinente
    When I set "Pertinente" for column "relevant" of row 1 of the "relevant_c2_g3" datagrid with a confirmation message
    Then the row 1 of the "relevant_c2_g3" datagrid should contain:
      | site    | relevant   |
      | Annecy  | Pertinente |
