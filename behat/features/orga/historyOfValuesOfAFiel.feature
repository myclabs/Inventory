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
    Then I should see "La saisie Europe | Marque B a été enregistrée pour la première fois par Administrateur."

  @javascript
  Scenario: Input history scenario, form with (repeated) subforms, creation and modification of an input
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
  # Onglet "Formulaires"
    And I open tab "Formulaires"
    And I open collapse "Zone | Marque"
    Then I should see the "aFGranularityConfig2" datagrid
    When I set "Formulaire avec sous-formulaires" for column "aF" of row 2 of the "aFGranularityConfig2" datagrid with a confirmation message
  # Pas besoin de modifier le statut de l'inventaire, on se trouve "au-dessus"
  # Accès à la saisie"
    When I open tab "Saisies"
    And I open collapse "Zone | Marque"
    Then I should see the "aFGranularity1Input2" datagrid
    When I click "Cliquer pour accéder" in the row 2 of the "aFGranularity1Input2" datagrid
  # Création de la saisie initiale
    When I fill in "s_f_n_r__chiffre_affaire" with "10"
    And I click "Enregistrer"

  @javascript
  Scenario: Input history scenario, display of history for various kinds of input fiels
    Given I am on "orga/cell/input/idCell/32/fromIdCell/1"
    And I wait for the page to finish loading
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