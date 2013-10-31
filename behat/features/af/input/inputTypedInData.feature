@dbFull
Feature: Input typed in data feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Correct interpretation of the difference between no input, value 0, and empty chain
    Given I am on "af/af/test/id/2"
    And I wait for the page to finish loading
  # Aucune saisie
    When I click "Aperçu des résultats"
    Then the field "chiffre_affaire" should have error: "Merci de renseigner ce champ."
  # Saisie " " dans champ obligatoire
    When I fill in "chiffre_affaire" with " "
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué, saisie incomplète. Vous pouvez renseigner les zones obligatoires manquantes maintenant ou plus tard."
    And the field "chiffre_affaire" should have error: "Merci de renseigner ce champ."
  # Saisie "0" dans champ obligatoire
    When I fill in "chiffre_affaire" with "0"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
  # Saisie vide dans champ obligatoire
    When I fill in "chiffre_affaire" with ""
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué, saisie incomplète. Vous pouvez renseigner les zones obligatoires manquantes maintenant ou plus tard."
    And the field "chiffre_affaire" should have error: "Merci de renseigner ce champ."

