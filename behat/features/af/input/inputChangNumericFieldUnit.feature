@dbFull
Feature: Modifying the unit

  Background:
    Given I am logged in

  @javascript @readOnly
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
