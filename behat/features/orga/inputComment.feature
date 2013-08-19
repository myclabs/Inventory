@dbFull
Feature: Organization input comment feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Organization input comment scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading

