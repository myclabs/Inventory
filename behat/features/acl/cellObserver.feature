@dbFull
Feature: Cell observer feature

  @javascript
  Scenario: Observer of a single cell
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "observateur.zone-marque@toto.com"
    And I fill in "password" with "observateur.zone-marque@toto.com"
    And I click "connection"
  # On tombe sur la page de la cellule
    Then I should see "Europe | Marque A Organisation avec données"
  # Accès à une saisie et à l'historique des valeurs d'un champ (suite à détection bug droits utilisateur)
    When I wait 5 seconds
    And I open collapse "Zone | Marque"
    And I click "Cliquer pour accéder" in the row 1 of the "aFGranularity2Input2" datagrid
    And I click element "#chiffre_affaireHistory .btn"
    Then I should see "Historique des valeurs"
    And I should see a "code:contains('10 k€ ± 15 %')" element
    And I click element "#chiffre_affaireHistory .btn"
  # TODO : "Vue globale" non cliquable dans le volet de navigation
  # Accès à l'onglet "Collectes"
    When I click "Quitter"
    And I open tab "Collectes"
    Then I should see the "inventories6" datagrid
    # And the "inventories6" datagrid should contain 2 row
  # TODO : statut de la collecte non éditable
  # Accès à l'onglet "Analyses"
    When I open tab "Analyses"
    Then I should see the "report" datagrid
  # Accès à l'onglet "Exports"
    When I open tab "Exports"
    # TODO : accès aux exports
  # Les autres onglets de la page d'une cellule sont absents
    And I should not see a "#container a:contains('Organisation')" element
    And I should not see a "#container a:contains('Rôles')" element
    And I should not see a "#container a:contains('Reconst. données')" element

  @javascript
  Scenario: Observer of several cells
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "observateur.site@toto.com"
    And I fill in "password" with "observateur.site@toto.com"
    And I click "connection"
  # On tombe sur le datagrid des cellules
    Then I should see the "listCells" datagrid
    And the row 1 of the "listCells" datagrid should contain:
      | label  | access      |
      | Annecy | Observateur |
  # Accès à une des cellules
    When I click "Cliquer pour accéder" in the row 1 of the "listCells" datagrid
    Then I should see "Annecy Organisation avec données"
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


