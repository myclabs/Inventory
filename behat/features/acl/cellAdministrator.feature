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
    Then I should see "Workspace avec données"
    When I click element "tr.workspace h4 a:contains('Workspace avec données')"
    And I wait for the page to finish loading
    Then I should see "Workspace avec données"
    And I should see "Vue globale"
  # Vérification onglets visibles et invisibles
    When I click element "h1 small a"
    And I wait for the page to finish loading
  # Vérification que, sous l'onglet "Paramétrage", l'utilisateur voit uniquement "Éléments" et "Pertinence"
    And I should see "Éléments"
    And I should see "Pertinence"
    And I should see "Reconstruction"
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
    Then I should see "Workspace avec données"
    When I click element "tr.workspace h4 a:contains('Workspace avec données')"
    And I wait for the page to finish loading
    Then I should see "Europe | Marque A"
  # Accès à une saisie et à l'historique des valeurs d'un champ (suite à détection bug droits utilisateur)
    When I click element "div[id='currentGranularity'] a.go-input"
    And I click element "#chiffre_affaireHistory"
    And I wait 2 seconds
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
    Then I should see "Workspace avec données"
    When I click element "tr.workspace h4 a:contains('Workspace avec données')"
    And I wait for the page to finish loading
    Then I should see "Administrateur Annecy"
    And I should see "Administrateur Chambéry"
  # Accès à une des cellules
    When I click "Administrateur Annecy"
    Then I should see "Workspace avec données"
    Then I should see "Annecy"
  # Accès à l'onglet "Analyses", vérification que l'utilisateur peut bien voir les analyses préconfigurées
    When I click element "div[id='currentGranularity'] i.fa-bar-chart-o"
    And I wait 5 seconds
    Then I should see "Chiffre d'affaire, par année"
    When I click "Chiffre d'affaire, par année"
    And I wait 8 seconds
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
    Then I should see "Workspace avec données"
    When I click element "tr.workspace h4 a:contains('Workspace avec données')"
    And I wait for the page to finish loading
    Then I should see "Workspace avec données"
    Then I should see "Europe | Marque A"
  # Vérification qu'on a bien accès à l'onglet "Paramétrage" et à ses sous-onglets
    When I click element "h1 small a"
  # On tombe sur l'onglet "Éléments"
    And I open collapse "Site"
    Then I should see the "listMemberssite" datagrid
  # Ajout d'un élément, saisie correcte (parent renseigné en partie)
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un élément à l'axe « Site »"
    When I fill in "listMemberssite_label_addForm" with "AAA"
    And I fill in "listMemberssite_ref_addForm" with "aaa"
    And I select "France" in s2 "listMemberssite_broaderpays_addForm"
    And I select "Marque A" in s2 "listMemberssite_broadermarque_addForm"
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
    Then I should see "Workspace avec données"
    When I click element "tr.workspace h4 a:contains('Workspace avec données')"
    And I wait for the page to finish loading
    When I click element "h1 small a"
  # Ajout et suppression d'un élément à l'axe "Pays"
    And I open collapse "Site"
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un élément à l'axe « Site »"
    When I fill in "listMemberssite_label_addForm" with "AAA"
    And I select "France" in s2 "listMemberssite_broaderpays_addForm"
    And I select "Marque A" in s2 "listMemberssite_broadermarque_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the "listMemberssite" datagrid should contain 3 row
    And the row 1 of the "listMemberssite" datagrid should contain:
      | label | ref | broaderpays | broadermarque |
      | AAA   | aaa | France      | Marque A      |
    When I click "Supprimer" in the row 1 of the "listMemberssite" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "listMemberssite" datagrid should contain 2 row
  # TODO : Suppression d'un élément entraînant la suppression de cellules associées à des DWs (par exemple un site).

  @javascript @readOnly
  Scenario: Cell administrator can edit an input
    Given I am logged in as "administrateur.global@toto.com"
    Given I am on "orga/cell/input/cell/3/fromCell/3/"
    And I wait 3 seconds
# On va sur la page de la cellule
    Then I should see "Saisie Europe | Marque A"
    When I fill in "chiffre_affaire" with "100"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    When I click "Terminer la saisie"
    Then the following message is shown and closed: "Saisie terminée."