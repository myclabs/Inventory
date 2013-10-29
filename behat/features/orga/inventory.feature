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
    And I open collapse "Année | Zone | Marque"
    Then I should see the "inventories6" datagrid
    And the row 5 of the "inventories6" datagrid should contain:
      | annee | zone   | marque   | inventoryStatus |
      | 2013  | Europe | Marque B | Non lancé       |
  # Édition du statut d'une collecte
    When I set "Ouvert" for column "inventoryStatus" of row 5 of the "inventories6" datagrid with a confirmation message
    Then the row 5 of the "inventories6" datagrid should contain:
      | annee | zone   | marque   | inventoryStatus |
      | 2013  | Europe | Marque B | Ouvert          |

  @javascript
  Scenario: Test Inventory filter scenario
  # Accès à l'onglet "Collectes"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Collectes"
    And I open collapse "Année | Zone | Marque"
    Then I should see the "inventories6" datagrid
    And the "inventories6" datagrid should contain 6 row
  # Filtre sur la marque
    When I open collapse "Filtres"
    And I select "Marque sans site" from "inventories6_marque_filterForm"
    And I click "Filtrer"
    Then the "inventories6" datagrid should contain 2 row

  @javascript
  Scenario: Percentage of complete and finished inputs scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Collectes"
    And I open collapse "Année | Site"
    Then I should see the "inventories7" datagrid
    Then the "inventories7" datagrid should contain a row:
      | annee | site   | inventoryStatus | advancementInput | advancementFinishedInput |
      | 2012  | Annecy | Ouvert          | 50%              | 25%                      |

  @javascript
  Scenario: Display of the inventory datagrid in a cell with a granularity smaller than or equal to that of inventories
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
  # On rend navigable la granularité "Année|Zone|Marque"
    When I open tab "Organisation"
    And I open tab "Niveaux"
    And I set "Navigable" for column "navigable" of row 6 of the "granularity" datagrid with a confirmation message
  # On recharge la page pour faire apparaître le volet de navigation
    And I reload the page
    And I click element "#goTo6"
    And I open tab "Collectes"
    And I open collapse "Année | Zone | Marque"
    Then I should see the "inventories6" datagrid
    And the "inventories6" datagrid should contain 1 row
    And the row 1 of the "inventories6" datagrid should contain:
      | inventoryStatus | advancementInput | advancementFinishedInput |
      | Ouvert          | 37%              | 12%                      |
