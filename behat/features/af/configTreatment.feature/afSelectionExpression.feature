@dbFull
Feature: AF algo selection expression feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an algo selection expression scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Traitement"
    And I open collapse "Algorithmes de sélection d’identifiant"
    And I open collapse "Expressions"
    Then I should see the "algoSelectionTextkeyExpression" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un algorithme de sélection d’identifiant de type « expression »"
  # Ajout, identifiant vide
    When I click "Valider"
    Then the field "algoSelectionTextkeyExpression_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "algoSelectionTextkeyExpression_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "algoSelectionTextkeyExpression_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé, expression vide
    When I fill in "algoSelectionTextkeyExpression_ref_addForm" with "champ_numerique"
    And I click "Valider"
    Then the field "algoSelectionTextkeyExpression_expression_addForm" should have error: "Il manque un opérateur dans l'expression « »."