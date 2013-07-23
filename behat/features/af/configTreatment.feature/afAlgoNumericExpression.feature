@dbFull
Feature: AF algo numeric expression feature

  Background:
    Given I am logged in

  @javascript
  Scenario: AF algo numeric expression creation
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Traitement"
    And I open collapse "Algorithmes numériques"
    And I open collapse "Expressions"
    Then I should see the "algoNumericExpression" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un algorithme numérique de type « expression »"
  # Ajout, identifiant vide
    When I click "Valider"
    Then the field "algoNumericExpression_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "algoNumericExpression_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "algoNumericExpression_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé, expression vide
    When I fill in "algoNumericExpression_ref_addForm" with "champ_numerique"
    And I click "Valider"
    Then the field "algoNumericExpression_ref_addForm" should have error: "Il manque un opérateur dans l'expression « »."
  # Ajout, identifiant déjà utilisé, expression non vide mais invalide
    When I fill in "algoNumericExpression_expression_addForm" with "a+(b+(c+d)"
    And I click "Valider"
    Then the field "algoNumericExpression_ref_addForm" should have error: "Au moins une parenthèse ouvrante n'est associée à aucune parenthèse fermante."
  # Ajout, identifiant déjà utilisé, expression correcte, unité vide
    When I fill in "algoNumericExpression_expression_addForm" with "a+b"
    And I click "Valider"
    Then the field "algoNumericExpression_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Ajout, identifiant correct
    When I fill in "algoNumericExpression_ref_addForm" with "test"And I click "Valider"