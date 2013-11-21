@dbFull
Feature: AF copy feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Copy of the combustion form, correct input, and test of the copied form scenario
    Given I am on "af/af/list"
    And I wait for the page to finish loading
    Then I should see the "listAF" datagrid
    And the "listAF" datagrid should contain 8 row
    And the row 1 of the "listAF" datagrid should contain:
      | label                                               | ref                                |
      | Combustion de combustible, mesuré en unité de masse | combustion_combustible_unite_masse |
    When I click "Dupliquer" in the row 1 of the "listAF" datagrid
    Then I should see the popup "Libellé et identifiant du nouveau formulaire (copie)"
    When I fill in "label" with "Copie combustion de combustible, mesuré en unité de masse"
    And I fill in "ref" with "combustion_combustible_unite_masse_copy"
    And I click element "#submit:contains('Dupliquer')"
    Then the following message is shown and closed: "Ajout effectué"
    And the "listAF" datagrid should contain 9 row
    And the row 9 of the "listAF" datagrid should contain:
      | label                                                     | ref                                     |
      | Copie combustion de combustible, mesuré en unité de masse | combustion_combustible_unite_masse_copy |
    When I click "Test" in the row 9 of the "listAF" datagrid
    And I select "Charbon" from "nature_combustible"
    And I fill in "quantite_combustible" with "10"
  # Formulaire copié : aperçu des résultats
    And I click "Aperçu des résultats"
    Then I should see "Total : 33,3 t équ. CO2"
  # Formulaire copié : enregistrement de la saisie
    When I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
  # Formulaire copié : accès détails calculs et vérification calculs corrects
    When I open tab "Détails calculs"
    And I open collapse "Formulaire maître"
    Then I should see "emissions_combustion Émissions liées à la combustion"
    When I open collapse "emissions_amont"
    Then I should see "Type : Expression"
    And I should see "quantite_combustible * fe_amont"
    When I click element "#combustion_combustible_unite_masse_copy__emissions_amont_wrapper .icon-zoom-in"
    Then I should see the popup "emissions_amont (Émissions liées aux processus amont de la combustion)"
    And I should see "quantite_combustible * fe_amont"
    And I should see "Produit"
    When I click "×"
    Then I should see "Valeur : 2,54 t équ. CO2 ± 20 %"

  @javascript
  Scenario: Copy of the combustion form, incorrect input
    Given I am on "af/af/list"
    And I wait for the page to finish loading
  # Essai de duplication avec libellé et identifiant vides
    And I click "Dupliquer" in the row 1 of the "listAF" datagrid
    And I click element "#submit:contains('Dupliquer')"
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Essai de duplication avec libellé vide et identifiant déjà utilisé
    When I click "Dupliquer" in the row 1 of the "listAF" datagrid
    And I fill in "ref" with "donnees_generales"
    And I click element "#submit:contains('Dupliquer')"
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Essai de duplication avec libellé non vide et identifiant déjà utilisé
    When I click "Dupliquer" in the row 1 of the "listAF" datagrid
    And I fill in "label" with "AAA"
    And I fill in "ref" with "donnees_generales"
    And I click element "#submit:contains('Dupliquer')"
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Copy of the forfait emission fonction marque form and test of the copied form scenario
    Given I am on "af/af/list"
    And I wait for the page to finish loading
    Then I should see the "listAF" datagrid
    And the "listAF" datagrid should contain 8 row
    And the row 8 of the "listAF" datagrid should contain:
      | label                                      | ref                       |
      | Forfait émissions en fonction de la marque | formulaire_forfait_marque |
    When I click "Dupliquer" in the row 8 of the "listAF" datagrid
    Then I should see the popup "Libellé et identifiant du nouveau formulaire (copie)"
    When I fill in "label" with "Copie forfait émissions en fonction de la marque"
    And I fill in "ref" with "formulaire_forfait_marque_copy"
    And I click element "#submit:contains('Dupliquer')"
    Then the following message is shown and closed: "Ajout effectué"
    And the "listAF" datagrid should contain 9 row
    And the row 9 of the "listAF" datagrid should contain:
      | label                                            | ref                            |
      | Copie forfait émissions en fonction de la marque | formulaire_forfait_marque_copy |
    When I click "Test" in the row 9 of the "listAF" datagrid
    And I click "Aperçu des résultats"
    Then I should see "Total : 1 t équ. CO2"
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

  @javascript
  Scenario: Copy of the formulaire avec tout type de champ form and test of the copied form scenario
    Given I am on "af/af/list"
    And I wait for the page to finish loading
    Then I should see the "listAF" datagrid
    And the "listAF" datagrid should contain 8 row
    And the row 5 of the "listAF" datagrid should contain:
      | label                              | ref                         |
      | Formulaire avec tout type de champ | formulaire_tous_types_champ |
    When I click "Dupliquer" in the row 5 of the "listAF" datagrid
    Then I should see the popup "Libellé et identifiant du nouveau formulaire (copie)"
    When I fill in "label" with "Copie formulaire avec tout type de champ"
    And I fill in "ref" with "formulaire_tous_types_champ_copy"
    And I click element "#submit:contains('Dupliquer')"
    Then the following message is shown and closed: "Ajout effectué"
    And the "listAF" datagrid should contain 9 row
    And the row 9 of the "listAF" datagrid should contain:
      | label                                    | ref                              |
      | Copie formulaire avec tout type de champ | formulaire_tous_types_champ_copy |
    When I click "Test" in the row 9 of the "listAF" datagrid
    And I fill in "Champ numérique" with "10"
    And I select "kg équ. CO2/bl" from "c_n_unit"
    And I select "Option 1" from "c_s_s_liste"
    And I check "c_s_s_bouton_opt_2"
    And I check "c_s_m_checkbox_opt_3"
    And I select "Option 4" from "c_s_m_liste"
    And I check "c_b"
    And I fill in "c_t_c" with "Bla"
    And I fill in "c_t_l" with "BlaBla"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."

  @javascript
  Scenario: Copy of the formulaire avec sous-formulaire repete contenant tout type de champ form and test of the copied form scenario
    Given I am on "af/af/list"
    And I wait for the page to finish loading
    Then I should see the "listAF" datagrid
    And the "listAF" datagrid should contain 8 row
    And the row 6 of the "listAF" datagrid should contain:
      | label                                                               | ref                               |
      | Formulaire avec sous-formulaire répété contenant tout type de champ | formulaire_s_f_r_tous_types_champ |
    When I click "Dupliquer" in the row 6 of the "listAF" datagrid
    Then I should see the popup "Libellé et identifiant du nouveau formulaire (copie)"
    When I fill in "label" with "Copie formulaire avec sous-formulaire répété contenant tout type de champ"
    And I fill in "ref" with "formulaire_s_f_r_tous_types_champ_copy"
    And I click element "#submit:contains('Dupliquer')"
    Then the following message is shown and closed: "Ajout effectué"
    And the "listAF" datagrid should contain 9 row
    And the row 9 of the "listAF" datagrid should contain:
      | label                                                                     | ref                                    |
      | Copie formulaire avec sous-formulaire répété contenant tout type de champ | formulaire_s_f_r_tous_types_champ_copy |
    When I click "Test" in the row 9 of the "listAF" datagrid
    And I click "Ajouter"
    And I click "Ajouter"
    And I fill in "s_f_r_t_t_c__c_n__1" with "10"
    And I select "kg équ. CO2/bl" from "s_f_r_t_t_c__c_n_unit__1"
    And I select "Option 1" from "s_f_r_t_t_c__c_s_s_liste__1"
    And I check "s_f_r_t_t_c__c_s_s_bouton__1_opt_2"
    And I check "s_f_r_t_t_c__c_s_m_checkbox__1_opt_3"
    And I select "Option 4" from "s_f_r_t_t_c__c_s_m_liste__1"
    And I check "s_f_r_t_t_c__c_b__1"
    And I fill in "s_f_r_t_t_c__c_t_c__1" with "Bla"
    And I fill in "s_f_r_t_t_c__c_t_l__1" with "BlaBla"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué, saisie incomplète. Vous pouvez renseigner les zones obligatoires manquantes maintenant ou plus tard."