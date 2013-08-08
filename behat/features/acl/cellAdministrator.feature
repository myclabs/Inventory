@dbFull
Feature: Cell administrator feature

  @javascript
  Scenario: Administrator of a single cell
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "administrateur.zone-marque@toto.com"
    And I fill in "password" with "administrateur.zone-marque@toto.com"
    And I click "connection"
  # On tombe sur la page de la cellule
    Then I should see "Europe | Marque A Organisation avec données"
    And I wait 2 seconds
    When I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity2Input8" datagrid
    And the "aFGranularity2Input8" datagrid should contain 4 row
  # Vérification que le libellé "Vue globale" est présent mais non cliquable
  # Voir "Organization navigation scenario"
    And I should see "Vue globale"
    And I should not see a "#navigationParent a:contains('Vue globale')" element

  @javascript
  Scenario: Administrator of several cells
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
    When I click "Cliquer pour accéder" in the row 1 of the "listCells" datagrid
    Then I should see "Annecy Organisation avec données"
    When I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity5Input8" datagrid
    And the "aFGranularity5Input8" datagrid should contain 2 row