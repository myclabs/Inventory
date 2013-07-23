@dbFull
Feature: AF composed condition for treatment feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of composed condition for treatment scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Traitement"
    And I open collapse "Conditions"
    And I open collapse "Conditions composées"
    Then I should see the "algoConditionExpression" datagrid