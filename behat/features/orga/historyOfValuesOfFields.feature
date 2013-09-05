@dbFull
Feature: History of values of a field feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Input history scenario, general data form, creation and modification of an input
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
  # Pas besoin de modifier le statut de l'inventaire, on se trouve "au-dessus"
  # Accès à la saisie"
    When I open tab "Saisies"
    And I open collapse "Zone | Marque"
    Then I should see the "aFGranularity1Input2" datagrid
    When I click "Cliquer pour accéder" in the row 2 of the "aFGranularity1Input2" datagrid
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
    And I click element "#chiffre_affaireHistory .btn"
    Then I should see a "code:contains('10 k€ ± 10 %')" element
  # Fermeture du popup d'historique
    When I click element "#chiffre_affaireHistory .btn"
    And I click "Quitter"
    And I open tab "Historique"
    Then I should see "La saisie Europe | Marque B a été enregistrée pour la première fois par Administrateur Système."

  @javascript
  Scenario: Input history scenario, display of history for various kinds of input fiels
  # Cellule : 2012 | Chambéry | Test affichage
    Given I am on "orga/cell/input/idCell/35/fromIdCell/1"
    And I wait for the page to finish loading
  # Attente pour voir si ça aide à passer sur serveur dédié
    And I wait 5 seconds
  # Champ numérique
    And I click element "#c_nHistory .btn"
    Then I should see a "code:contains('10 kg équ. CO2/m³ ± 15 %')" element
  # Champ de sélection simple "liste"
    When I click element "#c_nHistory .btn"
    And I click element "#c_s_s_listeHistory .btn"
    Then I should see a "code:contains('Option 1')" element
  # Champ de sélection simple "radio"
    When I click element "#c_s_s_listeHistory .btn"
    And I click element "#c_s_s_boutonHistory .btn"
    Then I should see a "code:contains('Option 1')" element
  # Champ de sélection multiple "checkbox"
    When I click element "#c_s_s_boutonHistory .btn"
    And I click element "#c_s_m_checkboxHistory .btn"
    Then I should see a "code:contains('Option 1, Option 2')" element
  # Champ de sélection multiple "liste"
    When I click element "#c_s_m_checkboxHistory .btn"
    And I click element "#c_s_m_listeHistory .btn"
    Then I should see a "code:contains('Option 1, Option 2')" element
  # Champ booléen
    When I click element "#c_s_m_listeHistory .btn"
    And I click element "#c_bHistory .btn"
    Then I should see a "code:contains('Coché')" element
  # Champ texte court
    When I click element "#c_bHistory .btn"
    And I click element "#c_t_cHistory .btn"
    Then I should see a "code:contains('Blabla')" element
  # Champ texte long
    When I click element "#c_t_cHistory .btn"
    And I click element "#c_t_lHistory .btn"
    Then I should see a "code:contains('Lorem ipsum dolor sit amet, consectetur adipisici…')" element
  # Fermeture dernier popup historique
    And I click element "#c_t_lHistory .btn"

  @javascript
  Scenario: Input history scenario, display of history for a repeated subform containing various types of fields, one repetition
  # Cellule : 2013 | Annecy | Test affichage
    Given I am on "orga/cell/input/idCell/44/fromIdCell/1"
    And I wait for the page to finish loading
  # Ajout attente pour voir si ça aide à faire passer le test sur serveur distant
    And I wait 5 seconds
  # Ajout 1 blocs de répétition
    And I click "Ajouter"
  # Champ numérique
    And I fill in "s_f_r_t_t_c__c_n__1" with "10"
    And I fill in "s_f_r_t_t_c__percentc_n__1" with "15"
  # Champs sélection simple
    And I select "Option 1" from "s_f_r_t_t_c__c_s_s_liste__1"
    And I check "s_f_r_t_t_c__c_s_s_bouton__1_opt_1"
  # Champs sélection multiple
    And I check "s_f_r_t_t_c__c_s_m_checkbox__1_opt_1"
    And I check "s_f_r_t_t_c__c_s_m_checkbox__1_opt_2"
    And I additionally select "Option 1" from "s_f_r_t_t_c__c_s_m_liste__1"
    And I additionally select "Option 2" from "s_f_r_t_t_c__c_s_m_liste__1"
  # Champ booléen
    And I check "s_f_r_t_t_c__c_b__1"
    And I fill in "s_f_r_t_t_c__c_t_c__1" with "Lorem ipsum dolor sit amet"
    And I fill in "s_f_r_t_t_c__c_t_l__1" with "Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi"
  # Enregistrement, complétude
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    And the "#tabs_tabInput .inputProgress .bar" element should contain "100%"
  # Ajout d'une répétition, enregistrement, vérification pourcentage avancement
    When I click "Ajouter"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie incomplète)."
    And the "#tabs_tabInput .inputProgress .bar" element should contain "50%"
  # Historiques des différents champs
    When I reload the page
    And I wait for the page to finish loading
  # Champ numérique
    When I click element "#s_f_r_t_t_c__c_nHistory__1 .btn"
    Then I should see a "code:contains('10 kg équ. CO2/m³ ± 15 %')" element
  # Champs de sélection simple
    And I click element "#s_f_r_t_t_c__c_nHistory__1 .btn"
    And I click element "#s_f_r_t_t_c__c_s_s_listeHistory__1 .btn"
    Then I should see a "code:contains('Option 1')" element
    When I click element "#s_f_r_t_t_c__c_s_s_listeHistory__1 .btn"
    And I click element "#s_f_r_t_t_c__c_s_s_boutonHistory__1 .btn"
    Then I should see a "code:contains('Option 1')" element
  # Champs de sélection multiple
    When I click element "#s_f_r_t_t_c__c_s_s_boutonHistory__1 .btn"
    And I click element "#s_f_r_t_t_c__c_s_m_checkboxHistory__1 .btn"
    Then I should see a "code:contains('Option 1, Option 2')" element
    When I click element "#s_f_r_t_t_c__c_s_m_checkboxHistory__1 .btn"
    And I click element "#s_f_r_t_t_c__c_s_m_listeHistory__1 .btn"
    Then I should see a "code:contains('Option 1, Option 2')" element
  # Champ booléen
    When I click element "#s_f_r_t_t_c__c_s_m_listeHistory__1 .btn"
    And I click element "#s_f_r_t_t_c__c_bHistory__1 .btn"
    Then I should see a "code:contains('Coché')" element
  # Champs texte
    When I click element "#s_f_r_t_t_c__c_bHistory__1 .btn"
    And I click element "#s_f_r_t_t_c__c_t_cHistory__1 .btn"
    Then I should see a "code:contains('Lorem ipsum dolor sit amet')" element
    When I click element "#s_f_r_t_t_c__c_t_cHistory__1 .btn"
    And I click element "#s_f_r_t_t_c__c_t_lHistory__1 .btn"
    Then I should see a "code:contains('Lorem ipsum dolor sit amet, consectetur adipisici…')" element
    When I click element "#s_f_r_t_t_c__c_t_lHistory__1 .btn"
  # Début de remplissage 2ème ligne, histoire de…
  # Champ numérique
    And I fill in "s_f_r_t_t_c__c_n__2" with "20"
    And I fill in "s_f_r_t_t_c__percentc_n__2" with "30"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie incomplète)."
    And the "#tabs_tabInput .inputProgress .bar" element should contain "57%"
  # Popups des valeurs saisies pour cette 2ème ligne
    When I click element "#s_f_r_t_t_c__c_nHistory__2 .btn"
    Then I should see a "code:contains('20 kg équ. CO2/m³ ± 30 %')" element
    When I click element "#s_f_r_t_t_c__c_nHistory__2 .btn"
    And I click element "#s_f_r_t_t_c__c_s_s_listeHistory__2 .btn"
    Then I should see a "code:contains('')" element
  # Suppression 2ème ligne
    When I click element "#s_f_r_t_t_c__s_f_r_t_t_c__2 .btn:contains('Supprimer')"

