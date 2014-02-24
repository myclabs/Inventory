@dbFull @readOnly
Feature: Validate and reopen an input

  Background:
    Given I am logged in

  @javascript
  Scenario: Validate input
    Given I am on "af/af/test/id/2"
    And I wait for the page to finish loading
    And I fill in "chiffre_affaire" with "12345"
    And I click "Enregistrer"
    And the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    When I click "Valider la saisie"
    Then the following message is shown and closed: "Saisie validée et fermée."
    And I should see "Saisie terminée"
    And I should see "Rouvrir la saisie"
    And I should not see "Enregistrer"
    And I should not see "Valider la saisie"

  @javascript
  Scenario: Reopen input
    Given I am on "af/af/test/id/2"
    And I wait for the page to finish loading
    And I fill in "chiffre_affaire" with "12345"
    And I click "Enregistrer"
    And the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    And I click "Valider la saisie"
    And the following message is shown and closed: "Saisie validée et fermée."
    When I click "Rouvrir la saisie"
    Then the following message is shown and closed: "Saisie réouverte."
    And I should see "Saisie complète"
    And I should not see "Rouvrir la saisie"
    And I should see "Enregistrer"
    And I should see "Valider la saisie"
