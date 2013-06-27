@dbEmpty
Feature: Units

  Background:
    Given I am logged in

  @javascript
  Scenario: Standard units
    Given I am on "unit/consult/standardunits"
  #  When I follow "Unités"
  #  And I follow "Unités standards"
    And I wait for the page to finish loading
    Then I should see "Unités standards"
    And I should see the "ListStandardUnits" datagrid
  # Format de séparateur de milliers
    And I should see "1 000 000"
  # Encodage de caractères
    And I should see "ℓ"
  # Séparateur de décimale
    And the row 7 of the "ListStandardUnits" datagrid should contain:
    | name     | ref | symbol | physicalQuantity | multiplier   | unitSystem|
    | gramme   | g   | g      | Masse            | 0,001        | International|

  @javascript
  Scenario: Extended units
    Given I am on "unit/consult/extendedunits"
    And I wait for the page to finish loading
    Then I should see "Unités étendues"
    And I should see the "ListExtendedUnit" datagrid
    # Multiplicateur (A MODIFIER CAR POUR L'INSTANT BUG)
    And the row 1 of the "ListExtendedUnit" datagrid should contain:
      | name                  | ref     | symbol     | multiplier|
      | gramme équivalent CO2 | g_co2e  | g équ. CO2 | 0         |

  @javascript
  Scenario: Discrete units
    Given I am on "unit/consult/discreteunits"
    And I wait for the page to finish loading
    Then I should see "Unités discrètes"
    And I should see the "ListDiscreteUnit" datagrid
    And the row 1 of the "ListDiscreteUnit" datagrid should contain:
      | name    | ref     |
      | animal  | animal  |

  @javascript
  Scenario: Physical quantities
    Given I am on "unit/consult/physicalquantities"
    And I wait for the page to finish loading
    Then I should see "Grandeurs physiques"
    And I should see the "ListPhysicalQuantity" datagrid
    And the row 2 of the "ListPhysicalQuantity" datagrid should contain:
      | name    | referenceUnit  | l | m | t | numeraire |
      | Longueur  | mètre        | 1 | 0 | 0 | 0         |

  @javascript
  Scenario: Standard units filter
    Given I am on "unit/consult/standardunits"
    And I wait for the page to finish loading
    And I open collapse "Filtres"
    And I fill in "ListStandardUnits_name_filterForm" with "pourcent"
    And I click "Filtrer"
    And I wait for the page to finish loading
    Then the "ListStandardUnits" datagrid should contain 1 row
    And the row 1 of the "ListStandardUnits" datagrid should contain:
      | name     |
      | pourcent |
