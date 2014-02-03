@dbFull
Feature: Family tree consult feature

  Background:
    Given I am logged in

  @javascript @readOnly
  Scenario: Consult family tree
    Given I am on "techno/family/tree"
    And I wait for the page to finish loading
    And I wait 5 seconds
  # Accès à la page de la famille en consultation
    And I click "Combustion de combustible, mesuré en unité de masse"
  # Vérification
    Then I should see "Combustion de combustible, mesuré en unité de masse"
