@dbFull
Feature: Cell contributor feature

  @javascript
  Scenario: Contributor of a single cell
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "contributeur.zone-marque@toto.com"
    And I fill in "password" with "contributeur.zone-marque@toto.com"
    And I click "connection"
  # On tombe sur la page de la cellule
    Then I should see "Europe | Marque A Organisation avec données"
    When I wait 2 seconds
    And I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity2Input8" datagrid
    And the "aFGranularity2Input8" datagrid should contain 2 row
  # Accès à l'onglet "Inventaires", édition du statut d'un inventaire
    When I open tab "Inventaires"
    Then I should see the "inventories6" datagrid
    When I set "En cours" for column "inventoryStatus" of row 1 of the "inventories6" datagrid with a confirmation message
  # Les autres onglets de la page d'une cellule sont absents
    # TODO And I should not see "Organisation"
    And I should not see "Rôles"
    And I should not see "Reconst. données"

  @javascript
  Scenario: Contributor of several cells
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "contributeur.site@toto.com"
    And I fill in "password" with "contributeur.site@toto.com"
    And I click "connection"
  # On tombe sur le datagrid des cellules
    Then I should see the "listCells" datagrid
    And the "listCells" datagrid should contain 2 row
    And the row 1 of the "listCells" datagrid should contain:
      | label  | access       |
      | Annecy | Contributeur |
  # Accès à une des cellules
    When I click "Cliquer pour accéder" in the row 1 of the "listCells" datagrid
    Then I should see "Annecy Organisation avec données"
    When I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity4Input8" datagrid
    And the "aFGranularity4Input8" datagrid should contain 1 row