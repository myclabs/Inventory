@dbFull
Feature: Organization inventory tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Edit inventory status scenario
  # Accès à l'onglet "Collectes" da la cellule globale
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
  # Descendre au niveau zone-marque (on descend sur Europe | Marque A")
    When I click element ".fa-plus"
    And I click element "#goTo2"
    Then I should see "Europe | Marque A Workspace avec données"
    When I open tab "Collectes"
    And I open collapse "Année | Zone | Marque"
    Then I should see the "inventories6" datagrid
    And the row 1 of the "inventories6" datagrid should contain:
      | annee | inventoryStatus |
      | 2012  | Ouvert          |
    When I set "Fermé" for column "inventoryStatus" of row 1 of the "inventories6" datagrid with a confirmation message
    Then the row 1 of the "inventories6" datagrid should contain:
      | annee | inventoryStatus |
      | 2012  | Fermé          |

  @javascript
  Scenario: View non editable inventory status scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Collectes"
    And I open collapse "Année | Site"
    Then I should see the "inventories7" datagrid
  # Vérification que ligne non éditable
    And I should not see a ".fa-pencil-square-o" element
  # Alors que pour le datagrid au-dessus c'est éditable
    When I close collapse "Année | Site"
    And I open collapse "Année | Zone | Marque"
    Then I should see the "inventories6" datagrid
    And I should see a ".fa-pencil-square-o" element
  # Descendre au niveau zone-marque (on descend sur Europe | Marque A")
    When I click element ".fa-plus"
    And I click element "#goTo2"
    Then I should see "Europe | Marque A Workspace avec données"
    When I open tab "Collectes"
    And I open collapse "Année | Site"
    Then I should see the "inventories7" datagrid
    And I should not see a ".fa-pencil-square-o" element
  # Alors que pour le datagrid au-dessus c'est éditable
    When I close collapse "Année | Site"
    And I open collapse "Année | Zone | Marque"
    Then I should see the "inventories6" datagrid
    And I should see a ".fa-pencil-square-o" element

  @javascript
  Scenario: Test inventory filter scenario
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
  Scenario: Display of the inventory datagrid and edit status in a cell with a granularity equal to that of inventories
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
  # On accède au datagrid des granularités
    When I open tab "Paramétrage"
    And I open tab "Niveaux"
  # On rend navigable la granularité "Année|Zone|Marque" (qui est la granularité des inventaires)
    Then I should see the "granularity" datagrid
    And the row 6 of the "granularity" datagrid should contain:
      | axes                | navigable     |
      | Année, Zone, Marque | Non navigable |
    When I set "Navigable" for column "navigable" of row 6 of the "granularity" datagrid with a confirmation message
  # On recharge la page pour faire apparaître le volet de navigation
    And I reload the page
  # On descend dans la cellule "2012 | Europe | Marque A"
    And I click element ".fa-plus"
    And I click element "#goTo6"
    And I open tab "Collectes"
    Then I should see the "inventories6" datagrid
    And the "inventories6" datagrid should contain 1 row
    And the row 1 of the "inventories6" datagrid should contain:
      | inventoryStatus | advancementInput | advancementFinishedInput |
      | Ouvert          | 37%              | 12%                      |

  @javascript
  Scenario: Display of the inventory datagrid in a cell with a granularity which is not greater than or equal to that of inventories
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
  # On descend au niveau site, on ne voit pas le datagrid des collectes
    And I click element ".fa-plus"
    And I click element "#goTo3"
    Then I should not see "Collectes"
  # Retour au niveau global
    When I click element ".fa-plus"
    And I click "Vue globale"
  # Modification de la granularité des inventaires
    And I open tab "Paramétrage"
    And I open tab "Informations générales"
    And I select "Année | Site" from "granularityForInventoryStatus"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Cette fois-ci, si on descend au niveau d'un site, on verra le datagrid des collectes
    When I click element ".fa-plus"
    And I click element "#goTo3"
    Then I should see "Collectes"
    When I open tab "Collectes"
    Then I should see the "inventories7" datagrid