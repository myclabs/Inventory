@dbFull
Feature: AF change component value interaction feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a change component value interaction scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Assignations de valeurs à des champs"
    Then I should see the "actionsSetValue" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une action d'assignation de valeur à un champ"
  # Ajout, sans rien préciser (champs présélectionnés par défaut)
    When I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."

  @javascript
  Scenario: Edition of a change component value interaction scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Modifications de l'état de composants"
    Then I should see the "actionsSetValue" datagrid

  @javascript
  Scenario: Deletion of a change component value interaction scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Modifications de l'état de composants"
    Then I should see the "actionsSetValue" datagrid