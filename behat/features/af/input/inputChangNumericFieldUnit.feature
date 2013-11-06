@dbFull
Feature: Combustion input feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Change input unit for a numeric field scenario
    Given I am on "af/af/test/id/1"
    And I wait for the page to finish loading
  # Saisie
    And I select "Charbon" from "nature_combustible"
    And I fill in "quantite_combustible" with "10"
    And I click "Aperçu des résultats"
    Then I should see "Total : 33,3 t équ. CO2"
  # On modifie l'unité du champ
    When I select "kg" from "quantite_combustible_unit"
    And I click "Aperçu des résultats"
    Then I should see "Total : 0,0333 t équ. CO2"

  @javascript
  Scenario: Check case where input unit cannot be changed
    Given I am on "af/edit/menu/id/1"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs numériques"
    And I set "Non modifiable" for column "unitSelection" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
    And I click "Test"
    Then I should see a "#quantite_combustible_unit" element
  #todo : tester que c'est effectivement non modifiable