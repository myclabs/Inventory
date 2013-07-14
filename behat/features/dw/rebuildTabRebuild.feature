@dbFull
Feature: Rebuild of dataware through the data rebuild tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Rebuild analysis data without launching form calculations
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Organisation"



  @javascript
  Scenario: Rebuild analysis data with launching form calculations