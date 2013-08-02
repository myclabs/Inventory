@dbFull
Feature: Input mandatory field feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Input mandatory field scenario
  # Formulaire des données générales : un seul champ "Chiffre d'affaires"
    Given I am on "af/af/test/id/5"
    And I wait for the page to finish loading
  # Affichage des messages des champs au clic sur "Aperçu des résultats"
    And I click "Aperçu des résultats"
    Then the field "champ_numerique" should have error: "Merci de renseigner ce champ."
    And the field "champ_selection_simple_liste" should have error: "Merci de renseigner ce champ."
    And the field "champ_selection_simple_bouton" should have error: "Merci de renseigner ce champ."
    And the field "champ_selection_multi_checkbox" should have error: "Merci de renseigner ce champ."
    And the field "champ_selection_multi_list" should have error: "Merci de renseigner ce champ."
    And the field "champ_texte_court" should have error: "Merci de renseigner ce champ."
    And the field "champ_texte_long" should have error: "Merci de renseigner ce champ."
  # Affichage des messages des champs au clic sur "Enregistrer"
    When I reload the page
    And I wait for the page to finish loading
  # Affichage des messages des champs au clic sur "Aperçu des résultats"
    And I check "champ_booleen"
    And I uncheck "champ_booleen"
    And I click "Enregistrer"
    Then the field "champ_numerique" should have error: "Merci de renseigner ce champ."
    And the field "champ_selection_simple_liste" should have error: "Merci de renseigner ce champ."
    And the field "champ_selection_simple_bouton" should have error: "Merci de renseigner ce champ."
    And the field "champ_selection_multi_checkbox" should have error: "Merci de renseigner ce champ."
    And the field "champ_selection_multi_list" should have error: "Merci de renseigner ce champ."
    And the field "champ_texte_court" should have error: "Merci de renseigner ce champ."
    And the field "champ_texte_long" should have error: "Merci de renseigner ce champ."
