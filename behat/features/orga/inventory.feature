@dbFull
Feature: Organization inventory tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Edit Inventory status
  # Accès à l'onglet "Collectes"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Collectes"
    Then I should see the "inventories6" datagrid
    And the row 1 of the "inventories6" datagrid should contain:
      | annee | zone   | marque   | inventoryStatus |
      | 2013  | Europe | Marque A | Non lancé |
  # Édition du statut d'une collecte
    When I set "En cours" for column "inventoryStatus" of row 1 of the "inventories6" datagrid with a confirmation message

  @javascript
  Scenario: Test Inventory filter
  # Accès à l'onglet "Collectes"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Collectes"
    Then I should see the "inventories6" datagrid
    And the "inventories6" datagrid should contain 2 row
  # Filtre sur la marque
    When I open collapse "Filtres"
    And I select "Marque sans site" from "inventories6_marque_filterForm"
    And I click "Filtrer"
    Then the "inventories6" datagrid should contain 1 row