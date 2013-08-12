@dbFull
Feature: Percentage of filled mandatory fields feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Percentage of filled mandatory fields scenario, all fiels are mandatory scenario
  # Accès interface de test
    Given I am on "af/af/test/id/5"
    And I wait for the page to finish loading
    And I fill in "c_n" with "10"
    And I click "Enregistrer"
  # 7 champs obligatoires, un seul rempli, le pourcentage de remplissage est de 100/7 = 14,29
    Then the "#tabs_tabInput .inputProgress .bar" element should contain "14%"

  @javascript
  Scenario: Percentage of filled mandatory fields scenario, some fiels are mandatory some are not scenario, 1
  # Accès à l'interface de configuration, onglet "Composants"
    Given I am on "af/edit/menu/id/5"
    And I wait for the page to finish loading
    And I open tab "Composants"
  # On rend certains champs facultatifs (en l'occurrence deux)
    And I open collapse "Champs de sélection simple"
    And I set "Facultatif" for column "required" of row 1 of the "selectSingleFieldDatagrid" datagrid with a confirmation message
    And I set "Facultatif" for column "required" of row 2 of the "selectSingleFieldDatagrid" datagrid with a confirmation message
    And I close collapse "Champs de sélection simple"
  # On accède à la saisie
    And I click "Test"
  # On remplit le champ numérigue
    And I fill in "c_n" with "10"
    And I click "Enregistrer"
  # 5 champs obligatoires, un rempli, le pourcentage doit être de 20%
    Then the "#tabs_tabInput .inputProgress .bar" element should contain "20%"

  @javascript
  Scenario: Percentage of filled mandatory fields scenario, some fiels are mandatory some are not scenario, 2
  # Accès à l'interface de configuration, onglet "Composants"
    Given I am on "af/edit/menu/id/5"
    And I wait for the page to finish loading
    And I open tab "Composants"
  # On rend certains champs facultatifs (en l'occurrence deux)
    And I open collapse "Champs de sélection multiple"
    And I set "Facultatif" for column "required" of row 1 of the "selectMultiFieldDatagrid" datagrid with a confirmation message
    And I set "Facultatif" for column "required" of row 2 of the "selectMultiFieldDatagrid" datagrid with a confirmation message
    And I close collapse "Champs de sélection multiple"
  # On accède à la saisie
    And I click "Test"
  # On remplit le champ numérigue
    And I fill in "c_n" with "10"
    And I click "Enregistrer"
  # 5 champs obligatoires, un rempli, le pourcentage doit être de 20%
    Then the "#tabs_tabInput .inputProgress .bar" element should contain "20%"
  # On remplit un champ de sélection multiple


  @javascript
  Scenario: Percentage of filled mandatory fields scenario, no field is mandatory scenario
  # Accès à l'interface de configuration, onglet "Composants"
    Given I am on "af/edit/menu/id/5"
    And I wait for the page to finish loading
    And I open tab "Composants"
  # On rend tous les champs facultatifs
    And I open collapse "Champs numériques"
    And I set "Facultatif" for column "required" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
    And I close collapse "Champs numériques"
    And I open collapse "Champs de sélection simple"
    And I set "Facultatif" for column "required" of row 1 of the "selectSingleFieldDatagrid" datagrid with a confirmation message
    And I set "Facultatif" for column "required" of row 2 of the "selectSingleFieldDatagrid" datagrid with a confirmation message
    And I close collapse "Champs de sélection simple"
    And I open collapse "Champs de sélection multiple"
    And I set "Facultatif" for column "required" of row 1 of the "selectMultiFieldDatagrid" datagrid with a confirmation message
    And I set "Facultatif" for column "required" of row 2 of the "selectMultiFieldDatagrid" datagrid with a confirmation message
    And I close collapse "Champs de sélection multiple"
    And I open collapse "Champs texte"
    And I set "Facultatif" for column "required" of row 1 of the "textFieldDatagrid" datagrid with a confirmation message
    And I set "Facultatif" for column "required" of row 2 of the "textFieldDatagrid" datagrid with a confirmation message
    And I close collapse "Champs texte"
  # On accède à la saisie
    And I click "Test"
  # On checke le champ booléen, histoire de modifier la saisie et donc de pouvoir l'enregistrer
    And I check "c_b"
    And I click "Enregistrer"
  # La saisie est complète
    Then the "#tabs_tabInput .inputProgress .bar" element should contain "100%"

  @javascript
  Scenario: Percentage of filled mandatory fields scenario, all fields are mandatory and completed scenario
    @skipped
    #6305 Les champs de sélection multiple de type "liste déroulante" apparaissent systématiquement comme incomplets
    Given I am on "af/af/test/id/5"
    And I wait for the page to finish loading
    And I fill in "c_n" with "10"
    And I select "Option 1" from "c_s_s_liste"
    And I check "c_s_s_bouton_opt_1"
    And I check "c_s_m_checkbox_opt_1"
    And I additionally select "Option 1" from "c_s_m_liste"
    And I fill in "c_t_c" with "Blabla"
    And I fill in "c_t_l" with "Blabla"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    And the "#tabs_tabInput .inputProgress .bar" element should contain "100%"
