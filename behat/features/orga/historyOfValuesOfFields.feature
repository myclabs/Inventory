@dbFull
Feature: History of values of a field feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Input history scenario, general data form, creation and modification of an input
    Given I am on "orga/cell/input/cell/4/fromCell/1/"
    And I wait for the page to finish loading
  # Création de la saisie initiale
    When I fill in "chiffre_affaire" with "10"
    And I fill in "percentchiffre_affaire" with "10"
    And I click "Enregistrer"
  # Modification de la saisie
    When I fill in "chiffre_affaire" with "20"
    And I fill in "percentchiffre_affaire" with "20"
    And I click "Enregistrer"
    And I reload the page
    And I wait for the page to finish loading
  # Ouverture du popup d'historique
    And I click element "#chiffre_affaireHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('10 k€ ± 10 %')" element
  # Fermeture du popup d'historique
    When I click element "#chiffre_affaireHistory"
    And I click "Quitter"
    And I wait for the page to finish loading
    Then I should see "La saisie Europe | Marque B a été enregistrée pour la première fois par Administrateur Système."
    And I should see "La saisie Europe | Marque B a été modifiée par Administrateur Système."

  @javascript
  Scenario: Input history scenario, display of history for various kinds of input fields
    # Cellule : 2012 | Chambéry | Test affichage
    Given I am on "orga/cell/input/cell/33/fromCell/1"
    And I wait for the page to finish loading
  # Champ numérique
    And I click element "#c_nHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('10 kg équ. CO2/m³ ± 15 %')" element
  # Champ de sélection simple "liste"
    When I click element "#c_nHistory"
    And I click element "#c_s_s_listeHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('Option 1')" element
  # Champ de sélection simple "radio"
    When I click element "#c_s_s_listeHistory"
    And I click element "#c_s_s_boutonHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('Option 1')" element
  # Champ de sélection multiple "checkbox"
    When I click element "#c_s_s_boutonHistory"
    And I click element "#c_s_m_checkboxHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('Option 1, Option 2')" element
  # Champ de sélection multiple "liste"
    When I click element "#c_s_m_checkboxHistory"
    And I click element "#c_s_m_listeHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('Option 1, Option 2')" element
  # Champ booléen
    When I click element "#c_s_m_listeHistory"
    And I click element "#c_bHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('Coché')" element
  # Champ texte court
    When I click element "#c_bHistory"
    And I click element "#c_t_cHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('Blabla')" element
  # Champ texte long
    When I click element "#c_t_cHistory"
    And I click element "#c_t_lHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('Lorem ipsum dolor sit amet, consectetur adipisici…')" element
  # Fermeture dernier popup historique
    And I click element "#c_t_lHistory"

  @javascript
  Scenario: Input history scenario, display of history for a repeated subform containing various types of fields, one repetition
  # Cellule : 2013 | Annecy | Test affichage
    Given I am on "orga/cell/input/cell/42/fromCell/1"
    And I wait for the page to finish loading
  # Ajout 1 blocs de répétition
    And I click "Ajouter"
  # Champ numérique
    And I fill in "s_f_r_t_t_c__1__c_n" with "10"
    And I fill in "s_f_r_t_t_c__1__percentc_n" with "15"
  # Champs sélection simple
    And I select "Option 1" from "s_f_r_t_t_c__1__c_s_s_liste"
    # On est obligé de passer par "click" à cause d'Angular :(
    And I click element "[name='s_f_r_t_t_c__1__c_s_s_bouton'][value='opt_1']"
  # Champs sélection multiple
    And I click element "[name='s_f_r_t_t_c__1__c_s_m_checkbox'][value='opt_1']"
    And I click element "[name='s_f_r_t_t_c__1__c_s_m_checkbox'][value='opt_2']"
    And I click element "[name='s_f_r_t_t_c__1__c_s_m_liste'][value='opt_1']"
    And I click element "[name='s_f_r_t_t_c__1__c_s_m_liste'][value='opt_2']"
  # Champ booléen
    And I click element "[name='s_f_r_t_t_c__1__c_b']"
    And I fill in "s_f_r_t_t_c__1__c_t_c" with "Lorem ipsum dolor sit amet"
    And I fill in "s_f_r_t_t_c__1__c_t_l" with "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi"
  # Enregistrement, complétude
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    And the "#tabs_tabInput .inputProgress .progress .progress-bar" element should contain "100%"
  # Ajout d'une répétition, enregistrement, vérification pourcentage avancement
    When I click "Ajouter"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué, saisie incomplète. Vous pouvez renseigner les zones obligatoires manquantes maintenant ou plus tard."
    And the "#tabs_tabInput .inputProgress .progress .progress-bar" element should contain "50%"
  # Historiques des différents champs
    When I reload the page
    And I wait for the page to finish loading
  # Champ numérique
    When I click element "#s_f_r_t_t_c__1__c_nHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('10 kg équ. CO2/m³ ± 15 %')" element
  # Champs de sélection simplek
    And I click element "#s_f_r_t_t_c__1__c_nHistory"
    And I click element "#s_f_r_t_t_c__1__c_s_s_listeHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('Option 1')" element
    When I click element "#s_f_r_t_t_c__1__c_s_s_listeHistory"
    And I click element "#s_f_r_t_t_c__1__c_s_s_boutonHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('Option 1')" element
  # Champs de sélection multiple
    When I click element "#s_f_r_t_t_c__1__c_s_s_boutonHistory"
    And I click element "#s_f_r_t_t_c__1__c_s_m_checkboxHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('Option 1, Option 2')" element
    When I click element "#s_f_r_t_t_c__1__c_s_m_checkboxHistory"
    And I click element "#s_f_r_t_t_c__1__c_s_m_listeHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('Option 1, Option 2')" element
  # Champ booléen
    When I click element "#s_f_r_t_t_c__1__c_s_m_listeHistory"
    And I click element "#s_f_r_t_t_c__1__c_bHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('Coché')" element
  # Champs texte
    When I click element "#s_f_r_t_t_c__1__c_bHistory"
    And I click element "#s_f_r_t_t_c__1__c_t_cHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('Lorem ipsum dolor sit amet')" element
    When I click element "#s_f_r_t_t_c__1__c_t_cHistory"
    And I click element "#s_f_r_t_t_c__1__c_t_lHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('Lorem ipsum dolor sit amet, consectetur adipisici…')" element
    When I click element "#s_f_r_t_t_c__1__c_t_lHistory"
  # Début de remplissage 2ème ligne, histoire de…
  # Champ numérique
    And I fill in "s_f_r_t_t_c__2__c_n" with "20"
    And I fill in "s_f_r_t_t_c__2__percentc_n" with "30"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué, saisie incomplète. Vous pouvez renseigner les zones obligatoires manquantes maintenant ou plus tard."
    And the "#tabs_tabInput .inputProgress .progress .progress-bar" element should contain "57%"
  # Popups des valeurs saisies pour cette 2ème ligne
    When I click element "#s_f_r_t_t_c__2__c_nHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('20 kg équ. CO2/m³ ± 30 %')" element
    When I click element "#s_f_r_t_t_c__2__c_nHistory"
    And I click element "#s_f_r_t_t_c__2__c_s_s_listeHistory"
    And I wait 2 seconds
    Then I should see a "code:contains('')" element
