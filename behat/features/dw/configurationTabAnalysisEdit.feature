@dbFull
Feature: General info tab analysis edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Add a granularity analysis (global granularity)
  # Accès à l'onglet "Informations générales"
    Given I am on "orga/workspace/edit/workspace/1"
    And I wait for the page to finish loading
    And I open tab "Config. Analyses"
  # Accès au datagrid des analyses pré-configurées au niveau global
    And I open collapse "Niveau organisationnel global"
    Then I should see the "datagridCellReports1" datagrid
  # Nouvelle analyse
    When I click element "a[href='orga/granularity/view-report/granularity/1/']"
    And I select "sum" from "typeSumRatioChoice"
    And I select "Camembert" from "displayType"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
  # Sauvegarde
    When I click "Enregistrer"
    Then I should see the popup "Enregistrer la configuration de l'analyse"
    When I fill in "Libellé" with "Analyse préconfigurée test"
    And I click element "#saveReport .btn:contains('Enregistrer')"
    And I click "Retour"
    And I open collapse "Niveau organisationnel global"
    Then I should see the "datagridCellReports1" datagrid
    And the "datagridCellReports1" datagrid should contain a row:
      | report |
      | Analyse préconfigurée test |

  @javascript
  Scenario: Delete a granularity analysis
    Given I am on "orga/workspace/edit/workspace/1"
    And I wait for the page to finish loading
    And I open tab "Config. Analyses"
    And I open collapse "Niveau organisationnel global"
    Then I should see the "datagridCellReports1" datagrid
    And the "datagridCellReports1" datagrid should contain 2 row
    When I click "Supprimer" in the row 1 of the "datagridCellReports1" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    And I wait 10 seconds
    Then the following message is shown and closed: "Suppression effectuée"
    And the "granularity1Report" datagrid should contain 1 row


