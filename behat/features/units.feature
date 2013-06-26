@dbEmpty
Feature: Units

  Background:
    Given I am logged in

  @javascript
  Scenario: Standard units
    Given I am on "unit/consult/standardunits"
    And I wait for the page to finish loading
    Then I should see "Unités standards"
    And I should see the "ListStandardUnits" datagrid
  # Format de séparateur de milliers
    And I should see "1 000 000"
  # Encodage de caractères
    And I should see "ℓ"
  # Séparateur de décimale
    And the row 7 of the "ListStandardUnits" datagrid should contain:
    | name     | ref | symbol | physicalQuantity | multiplier   | unitSystem
    | gramme   | g   | g      | Masse            | 0,001        | International

  @javascript
  Scenario: Extended units
    Given I am on "unit/consult/extendedunits"
    And I wait for the page to finish loading
    Then I should see "Unités étendues"
    And I should see the "ListExtendedUnit" datagrid
    # Multiplicateur (A MODIFIER CAR POUR L'INSTANT BUG)
    And the row 1 of the "ListExtendedUnit" datagrid should contain:
      | name                  | ref     | symbol     | multiplier
      | gramme équivalent CO2 | g_co2e  | g équ. CO2 | 0