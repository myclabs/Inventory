@dbFull @readOnly
Feature: Input mandatory field display feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Input mandatory field display scenario
  # Accès interface de test, formulaire "Formulaire avec tout type de champ"
    Given I am on "af/af/test/id/5"
    And I wait for the page to finish loading
  # Affichage des messages des champs au clic sur "Aperçu des résultats"
    And I check "c_b"
    And I uncheck "c_b"
    And I click "Aperçu des résultats"
    Then the field "c_n" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "c_s_s_liste" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "c_s_s_bouton" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "c_s_m_checkbox" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "c_s_m_liste" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "c_t_c" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "c_t_l" should have error: "Champ obligatoire pour atteindre le statut : complet."
  # Affichage des messages des champs au clic sur "Enregistrer"
    When I reload the page
    And I wait for the page to finish loading
    And I check "c_b"
    And I uncheck "c_b"
    And I click "Enregistrer"
    Then the field "c_n" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "c_s_s_liste" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "c_s_s_bouton" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "c_s_m_checkbox" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "c_s_m_liste" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "c_t_c" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "c_t_l" should have error: "Champ obligatoire pour atteindre le statut : complet."

  @javascript
  Scenario: Input mandatory field display for a repeated subform scenario
  # Accès interface de test
    Given I am on "af/af/test/id/6"
    And I wait for the page to finish loading
  # Ajout d'un bloc de répétition
    And I click "Ajouter"
  # Affichage des messages des champs au clic sur "Aperçu des résultats"
    And I click "Aperçu des résultats"
    Then the field "s_f_r_t_t_c__1__c_n" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "s_f_r_t_t_c__1__c_s_s_liste" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "s_f_r_t_t_c__1__c_s_s_bouton" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "s_f_r_t_t_c__1__c_s_m_checkbox" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "s_f_r_t_t_c__1__c_s_m_liste" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "s_f_r_t_t_c__1__c_t_c" should have error: "Champ obligatoire pour atteindre le statut : complet."
    And the field "s_f_r_t_t_c__1__c_t_l" should have error: "Champ obligatoire pour atteindre le statut : complet."
