@dbFull
Feature: Rebuild feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Simple rebuild of workspace
  # Accès à l'onglet "Reconstruction"
    Given I am on "orga/workspace/edit/workspace/1"
    And I wait for the page to finish loading
    And I open tab "Reconstruction"
    And I wait 2 seconds
    Then I should see "Régénération des données d'analyse"
  # Régénération simple du workspace global
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours."
