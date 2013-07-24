@dbFull
Feature: AF composed condition for interaction feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an composed condition for interaction scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Conditions composées"
    Then I should see the "conditionsExpression" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une condition élémentaire"
  # Ajout, sans rien préciser
    When I click "Valider"
    Then the field "conditionsExpression_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "conditionsExpression_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "conditionsExpression_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
    # Ajout, identifiant déjà utilisé, expression vide
    When I fill in "conditionsExpression_ref_addForm" with "test"
    And I click "Valider"
    Then the field "conditionsExpression_expression_addForm" should have error: "Il manque un opérateur dans l'expression « »."
  # Ajout, identifiant déjà utilisé, expression incorrecte
    When I fill in "conditionsExpression_expression_addForm" with "a|(b|(c|d)"
    And I click "Valider"
    Then the field "conditionsExpression_expression_addForm" should have error: "Au moins une parenthèse ouvrante n'est associée à aucune parenthèse fermante."
  # Ajout, identifiant déjà utilisé, expression correcte
    When I fill in "conditionsExpression_expression_addForm" with "a&(b|c)&d"
    And I click "Valider"
    # TODO…
  # Ajout, saisie correcte
    Then the following message is shown and closed: "Ajout effectué."
    And the row 1 of the "conditionsExpression" datagrid should contain:
      | ref  |
      | test |
    When I click "Expression" in the row 1 of the "conditionsExpression" datagrid
    Then I should see the popup "Expression"
    And I should see "a & (b | c) & d"

  @javascript
  Scenario: Edition of an elementary condition for interaction scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Conditions composées"
    Then I should see the "conditionsExpression" datagrid

  @javascript
  Scenario: Deletion of an elementary condition for interaction scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Conditions composées"
    Then I should see the "conditionsExpression" datagrid