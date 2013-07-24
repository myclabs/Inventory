@dbFull
Feature: Change component state interaction feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a change component state interaction scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Modifications de l'état de composants"
    Then I should see the "actionsSetState" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une action de modification de l'état d'un composant"
  # Ajout, sans rien préciser (champs présélectionnés par défaut)
    When I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."

  @javascript
  Scenario: Edition of a change component state interaction scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Modifications de l'état de composants"
    Then I should see the "actionsSetState" datagrid

  @javascript
  Scenario: Deletion of a change component state interaction scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Modifications de l'état de composants"
    Then I should see the "actionsSetState" datagrid