@dbFull
Feature: Non mandatory field used for treatment feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Non mandatory numeric field used for treatment scenario
  # Page de configuration du formulaire "Combustion de combustible"
    Given I am on "af/edit/menu/id/1"
    And I wait for the page to finish loading
    And I open tab "Composants"
  # On rend facultatif le champ de saisie numérique
    And I open collapse "Champs numériques"
    And I set "Facultatif" for column "required" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
  # On teste la saisie
    And I click "Test"
    And I select "Charbon" from "nature_combustible"
    And I click "Aperçu des résultats"
  # Le résultat est affiché valant zéro, c'est-à-dire que le champ numérique vide est interprété comme s'il était rempli avec la valeur zéro.
    Then I should see "Total : 0 t équ. CO2"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."

  @javascript
  Scenario: Non mandatory selection field used for treatment scenario
  # Page de configuration du formulaire "Combustion de combustible"
    Given I am on "af/edit/menu/id/1"
    And I wait for the page to finish loading
    And I open tab "Composants"
  # On rend facultatif le champ de sélection simple
    And I open collapse "Champs de sélection simple"
    And I set "Facultatif" for column "required" of row 1 of the "selectSingleFieldDatagrid" datagrid with a confirmation message
  # On teste la saisie
    And I click "Test"
    And I fill in "quantite_combustible" with "10"
    And I click "Aperçu des résultats"
  # TODO : exception non capturée.
