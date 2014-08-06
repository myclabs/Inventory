@dbFull
Feature: Modifying the unit

  Background:
    Given I am logged in

  @javascript @readOnly
  Scenario: Change input unit for a numeric field scenario
    Given I am on "af/af/test/id/1"
    And I wait for the page to finish loading
  # Saisie
    And I fill in "quantite_combustible" with "10"
    And I click element "select[name='nature_combustible'] [value='0']"
        # Nécéssaire pour que Angular détecte le changement.
    And I click "Aperçu des résultats"
    And I wait 3 seconds
    Then I should see "Total : 33,3 t équ. CO2"
  # On modifie l'unité du champ
    When I click element "select[name='quantite_combustible_unit'] [value='kg']"
    And I click "Aperçu des résultats"
    And I wait 3 seconds
    Then I should see "Total : 0,0333 t équ. CO2"
