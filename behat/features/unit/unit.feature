@dbEmpty
Feature: Unit feature

  Background:
    Given I am logged in

  @javascript @readOnly
  Scenario: Display of standard units page
    Given I am on "unit/consult/standardunits"
  # Tentative d'utiliser le menu de haut de page
    # When I click element ".navbar a:contains('Référentiels')"
    # And I click element ".navbar a:contains('Unités standards')"
    And I wait for the page to finish loading
    Then I should see "Unités standards"
    And I should see the "ListStandardUnits" datagrid
  # Encodage de caractères
    And I should see "ℓ"
  # Séparateur de décimale
    And the row 8 of the "ListStandardUnits" datagrid should contain:
    | name     | ref | symbol | physicalQuantity | unitSystem    |
    | gramme   | g   | g      | Masse            | International |

  @javascript @readOnly
  Scenario: Display of discrete units page
    Given I am on "unit/consult/discreteunits"
    And I wait for the page to finish loading
    Then I should see "Unités discrètes"
    And I should see the "discreteUnits" datagrid
    And the row 1 of the "discreteUnits" datagrid should contain:
      | name    | ref     |
      | animal  | animal  |
