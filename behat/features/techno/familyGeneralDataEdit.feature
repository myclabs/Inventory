@dbFull
Feature: Family general data edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Family edit general data scenario, correct input
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
    Then I should see "Famille test non vide"
    When I open tab "Général"
  # Vérification du contenu des différents champs du formulaire "Général"
    And the "Libellé" field should contain "Famille test non vide"
    And the "Identifiant" field should contain "famille_test"
    And the "Unité" field should contain "kg_co2e.t^-1"
  # Modifications
    When I fill in "Libellé" with "Famille test modifiée"
    And I fill in "Identifiant" with "famille_test_modifiee"
    And I fill in "Unité" with "t_co2e.kg^-1"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
    And the "Libellé" field should contain "Famille test modifiée"
    And the "Identifiant" field should contain "famille_test_modifiee"
    And the "Unité" field should contain "t_co2e.kg^-1"
  # TODO : autoriser la modification de l'unité en une unité non compatible ?
  # Modification du libellé seul
    When I fill in "Libellé" with "Famille test modifiée une seconde fois"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."

  @javascript
  Scenario: Family edit general data scenario, incorrect input
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
    When I open tab "Général"
  # Libellé et identifiant et unité vides
    And I fill in "Libellé" with ""
    And I fill in "Identifiant" with ""
    And I fill in "Unité" with ""
    And I click "Enregistrer"
    Then the field "Libellé" should have error: "Merci de renseigner ce champ."
    And the field "Identifiant" should have error: "Merci de renseigner ce champ."
    And the field "Unité" should have error: "Merci de renseigner ce champ."
  # Libellé non vide, identifiant caractères non autorisés, unité invalide
    When I fill in "Libellé" with "Test"
    And I fill in "Identifiant" with "bépo"
    And I fill in "Unité" with "auie"
    And I click "Enregistrer"
    Then the field "Identifiant" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
    And the field "Unité" should have error: "Merci de saisir un identifiant d'unité valide."
  # Libellé non vide, identifiant déjà utilisé, unité valide mais non compatible
    When I fill in "Identifiant" with "combustion_combustible_unite_masse"
    And I fill in "Unité" with "m2"
    And I click "Enregistrer"
    Then the field "Identifiant" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # TODO : modifier le message d'erreur pour préciser que le problème n'est pas que l'unité est invalide, mais qu'elle est incompatible avec l'unité initiale.
    And the field "Unité" should have error: "Merci de saisir un identifiant d'unité valide."

