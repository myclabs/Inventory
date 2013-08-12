@dbFull
Feature: Organization inventory tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Edit Inventory status
  # Accès à l'onglet "Collectes"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Collectes"
    Then I should see the "inventories6" datagrid
    And the row 5 of the "inventories6" datagrid should contain:
      | annee | zone   | marque   | inventoryStatus |
      | 2013  | Europe | Marque B | Non lancé       |
  # Édition du statut d'une collecte
    When I set "En cours" for column "inventoryStatus" of row 4 of the "inventories6" datagrid with a confirmation message
    Then the row 5 of the "inventories6" datagrid should contain:
      | annee | zone   | marque   | inventoryStatus |
      | 2013  | Europe | Marque B | En cours        |

  @javascript
  Scenario: Test Inventory filter
  # Accès à l'onglet "Collectes"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Collectes"
    Then I should see the "inventories6" datagrid
    And the "inventories6" datagrid should contain 6 row
  # Filtre sur la marque
    When I open collapse "Filtres"
    And I select "Marque sans site" from "inventories6_marque_filterForm"
    And I click "Filtrer"
    Then the "inventories6" datagrid should contain 2 row

  @javascript
  Scenario: Percentage of complete and finished inputs
    Given I am on "orga/cell/details/idCell/5"
    And I wait for the page to finish loading
    And I open tab "Collectes"
    Then the "inventories6" datagrid should contain a row:
      | annee | inventoryStatus | advancementInput | advancementFinishedInput |
      | 2012  | En cours        | 66%              | 33%                      |