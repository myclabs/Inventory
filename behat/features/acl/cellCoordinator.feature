@dbFull
Feature: Cell coordinator feature

  @javascript
  Scenario: Coordinator of a single cell
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login
    When I fill in "email" with "coordinateur.zone-marque@toto.com"
    And I fill in "password" with "coordinateur.zone-marque@toto.com"
    And I click "connection"
  # On tombe sur la page de la cellule
    Then I should see "Europe | Marque A Workspace avec données"
  # Accès à une saisie et à l'historique des valeurs d'un champ (suite à détection bug droits utilisateur)
    When I wait 5 seconds
    And I open collapse "Zone | Marque"
    And I click "Cliquer pour accéder" in the row 1 of the "aFGranularity2Input2" datagrid
    And I click element "#chiffre_affaireHistory .btn"
    Then I should see "Historique des valeurs"
    And I should see a "code:contains('10 k€ ± 15 %')" element
  # Accès à l'onglet "Collectes", édition du statut d'une collecte
    When I click "Quitter"
    And I open tab "Collectes"
    And I open collapse "Année | Zone | Marque"
    Then I should see the "inventories6" datagrid
    When I set "Ouvert" for column "inventoryStatus" of row 1 of the "inventories6" datagrid with a confirmation message
  # Les autres onglets de la page d'une cellule sont absents
  #TODO (2.10) And I should not see "Paramétrage"
    And I should not see "Rôles"
    And I should not see "Reconstruction"

  @javascript
  Scenario: Coordinator of several cells
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login
    When I fill in "email" with "coordinateur.site@toto.com"
    And I fill in "password" with "coordinateur.site@toto.com"
    And I click "connection"
  # On tombe sur le datagrid des cellules
    Then I should see the "listCells" datagrid
    And the "listCells" datagrid should contain 2 row
    And the row 1 of the "listCells" datagrid should contain:
      | label  | access       |
      | Annecy | Coordinateur |
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
      | 2012             | 10           | 15               |
    And the row 2 of the "reportValues" datagrid should contain:
      | valueAxiso_annee | valueDigital | valueUncertainty |
      | 2013             | 10           | 15               |