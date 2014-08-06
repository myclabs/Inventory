@dbFull @readOnly
Feature: Validate and reopen an input

  Background:
    Given I am logged in

  @javascript
  Scenario: Finish input
    Given I am on "af/af/test/id/2"
    And I wait for the page to finish loading
    And I fill in "chiffre_affaire" with "12345"
      # Nécéssaire pour que Angular détecte le changement.
    And I click element "select[name='chiffre_affaire_unit'] [value='euro']"
    And I click element "select[name='chiffre_affaire_unit'] [value='kiloeuro']"
    And I click "Enregistrer"
    And the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    Then I should see "La saisie est complète, vous devez cliquer sur \"Terminer la saisie\" si vous ne comptez plus y apporter de modifications."
    When I click "Terminer la saisie"
    Then the following message is shown and closed: "Saisie terminée."
    And I should not see "La saisie est complète, vous devez cliquer sur \"Terminer la saisie\" si vous ne comptez plus y apporter de modifications."
    And I should see "Saisie terminée"
    And the button "Enregistrer" must be disabled
    And the button "Terminer la saisie" must be disabled
