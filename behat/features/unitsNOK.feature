@dbEmpty
Feature: UnitsNOK

  Background:
    Given I am logged in

  @javascript
  Scenario: Standard units filter
    Given I am on "unit/consult/standardunits"
    And I wait for the page to finish loading
    And I follow "Filtres"
    And I fill in "ListStandardUnits_name_filterForm" with "kilo"
    And I press "Filtrer"
    Then I should not see "gramme"
