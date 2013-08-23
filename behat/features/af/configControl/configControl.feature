@dbFull
Feature: AF config control feature

  Background:
    Given I am logged in

  @javascript
  Scenario: AF config control empty form scenario
    Given I am on "af/edit/menu/id/7"
    And I wait for the page to finish loading
  # Vérification qu'on se trouve bien sur le bon formulaire
    Then I should see "Formulaire vide"
  # Onglet "Contrôle"
    When I open tab "Contrôle"
    And I click "Contrôler"
    Then I should see "Avertissement"
    And I should see "Le groupe « root_group » est vide."
    And I should see "L'algorithme d'exécution du formulaire est vide. "

  @javascript
  Scenario: AF config control general data scenario
    Given I am on "af/edit/menu/id/2"
    And I wait for the page to finish loading
    And I open tab "Contrôle"
    And I click "Contrôler"
    Then I should see "Aucune erreur détectée."

  @javascript
  Scenario: AF config control combustion scenario
    Given I am on "af/edit/menu/id/1"
    And I wait for the page to finish loading
    And I open tab "Contrôle"
    And I click "Contrôler"
    Then I should see "Aucune erreur détectée."
