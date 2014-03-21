@dbFull @readOnly
Feature: Forfait emissions input feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Forfait emissions input scenario
  # Accès au formulaire
    Given I am on "af/af/test/id/8"
    And I wait for the page to finish loading
  # Saisie et enregistrement
    When I fill in "Champ sans effet" with "0"
    And I click "Enregistrer"
    And I open tab "Résultats"
    Then I should see "Total : 1 t équ. CO2"
  # Détails calculs
    When I open tab "Détails calculs"
    And I open collapse "Formulaire maître"
    And I open collapse "algo_numerique_forfait_marque"
    Then I should see "Marque : marque A"
