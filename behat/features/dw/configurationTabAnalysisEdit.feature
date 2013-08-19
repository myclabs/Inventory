@dbFull
Feature: Configuration tab analysis edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Add a granularity analysis (global granularity)
  # Accès à l'onglet "Configuration"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Configuration"
  # Accès au datagrid des analyses pré-configurées au niveau global
    And I open collapse "Niveau organisationnel global"
    Then I should see the "granularity1Report" datagrid
  # Nouvelle analyse
    When I click "Nouvelle analyse"
    And I click element "#indicatorRatio_indicator"
    And I select "Camembert" from "chartType"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
  # Sauvegarde
    When I click "Enregistrer"
    Then I should see the popup "Enregistrer la configuration de l'analyse"
    When I fill in "Libellé" with "Analyse préconfigurée test"
    And I click element "#saveReport .btn:contains('Enregistrer')"
    And I click "Retour"
    And I open collapse "Niveau organisationnel global"
    Then I should see the "granularity1Report" datagrid
    And the "granularity1Report" datagrid should contain a row:
      | label |
      | Analyse préconfigurée test |

  @javascript
  Scenario: Delete a granularity analysis
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Configuration"
    And I open collapse "Niveau organisationnel global"
    Then I should see the "granularity1Report" datagrid
    And the "granularity1Report" datagrid should contain 2 row
    When I click "Supprimer" in the row 1 of the "granularity1Report" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer" 
    Then the following message is shown and closed: "Suppression effectuée"
    And the "granularity1Report" datagrid should contain 1 row


