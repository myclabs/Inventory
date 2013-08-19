@dbFull
Feature: Input mandatory field display feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Input mandatory field display scenario
  # Accès interface de test
    Given I am on "af/af/test/id/5"
    And I wait for the page to finish loading
  # Affichage des messages des champs au clic sur "Aperçu des résultats"
    And I click "Aperçu des résultats"
    Then the field "c_n" should have error: "Merci de renseigner ce champ."
    And the field "c_s_s_liste" should have error: "Merci de renseigner ce champ."
    And the field "c_s_s_bouton" should have error: "Merci de renseigner ce champ."
    And the field "c_s_m_checkbox" should have error: "Merci de renseigner ce champ."
    And the field "c_s_m_liste" should have error: "Merci de renseigner ce champ."
    And the field "c_t_c" should have error: "Merci de renseigner ce champ."
    And the field "c_t_l" should have error: "Merci de renseigner ce champ."
  # Affichage des messages des champs au clic sur "Enregistrer"
    When I reload the page
    And I wait for the page to finish loading
  # Affichage des messages des champs au clic sur "Aperçu des résultats"
    And I check "c_b"
    And I uncheck "c_b"
    And I click "Enregistrer"
    Then the field "c_n" should have error: "Merci de renseigner ce champ."
    And the field "c_s_s_liste" should have error: "Merci de renseigner ce champ."
    And the field "c_s_s_bouton" should have error: "Merci de renseigner ce champ."
    And the field "c_s_m_checkbox" should have error: "Merci de renseigner ce champ."
    And the field "c_s_m_liste" should have error: "Merci de renseigner ce champ."
    And the field "c_t_c" should have error: "Merci de renseigner ce champ."
    And the field "c_t_l" should have error: "Merci de renseigner ce champ."

  @javascript
  Scenario: Input mandatory field display for a repeated subform scenario
  # Accès interface de test
    Given I am on "af/af/test/id/6"
    And I wait for the page to finish loading
  # Attente pour voir si ça débloque traitement distant
    And I wait 2 seconds
  # Ajout d'un bloc de répétition
    And I click "Ajouter"
  # Affichage des messages des champs au clic sur "Aperçu des résultats"
    And I click "Aperçu des résultats"
    Then the field "s_f_r_t_t_c__c_n__1" should have error: "Merci de renseigner ce champ."
    And the field "s_f_r_t_t_c__c_s_s_liste__1" should have error: "Merci de renseigner ce champ."
    And the field "s_f_r_t_t_c__c_s_s_bouton__1" should have error: "Merci de renseigner ce champ."
    And the field "s_f_r_t_t_c__c_s_m_checkbox__1" should have error: "Merci de renseigner ce champ."
    And the field "s_f_r_t_t_c__c_s_m_liste__1" should have error: "Merci de renseigner ce champ."
    And the field "s_f_r_t_t_c__c_t_c__1" should have error: "Merci de renseigner ce champ."
    And the field "s_f_r_t_t_c__c_t_l__1" should have error: "Merci de renseigner ce champ."
