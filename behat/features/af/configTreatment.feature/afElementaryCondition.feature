@dbFull
Feature: AF elementary condition for treatment feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an elementary condition for treatment scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Traitement"
    And I open collapse "Conditions"
    And I open collapse "Conditions élémentaires"
    Then I should see the "algoConditionElementary" datagrid