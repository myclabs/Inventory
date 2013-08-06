@dbFull
Feature: AF composed condition for interaction feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an composed condition for interaction scenario, correct input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Conditions composées"
    Then I should see the "conditionsExpression" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une condition composée"
  # Ajout, saisie correcte
    When I fill in "conditionsExpression_ref_addForm" with "aaa"
    And I fill in "conditionsExpression_expression_addForm" with "a&(b|c)&d"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Conditions composées affichées dans l'ordre d'ajout
    And the row 2 of the "conditionsExpression" datagrid should contain:
      | ref  |
      | aaa |
    When I click "Expression" in the row 2 of the "conditionsExpression" datagrid
    Then I should see the popup "Expression"
    And I should see "a & (b | c) & d"

  @javascript
  Scenario: Creation of an composed condition for interaction scenario, incorrect input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Conditions composées"
    Then I should see the "conditionsExpression" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une condition composée"
  # Ajout, sans rien préciser
    When I click "Valider"
    Then the field "conditionsExpression_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "conditionsExpression_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "conditionsExpression_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé, expression vide
    When I fill in "conditionsExpression_ref_addForm" with "condition_composee_interactions"
    And I click "Valider"
    Then the field "conditionsExpression_expression_addForm" should have error: " Il manque un opérateur dans l'expression «  »."
  # Ajout, identifiant déjà utilisé, expression incorrecte
    When I fill in "conditionsExpression_expression_addForm" with "a|(b|(c|d)"
    And I click "Valider"
    Then the field "conditionsExpression_expression_addForm" should have error: "Au moins une parenthèse ouvrante n'est associée à aucune parenthèse fermante."
  # Ajout, identifiant déjà utilisé, expression correcte
    When I fill in "conditionsExpression_expression_addForm" with "a&(b|c)&d"
    And I click "Valider"
    Then the field "conditionsExpression_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of an composed condition for interaction scenario, correct input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Conditions composées"
    Then I should see the "conditionsExpression" datagrid
  # Vérification contenu initial
    And the row 1 of the "conditionsExpression" datagrid should contain:
      | ref                             |
      | condition_composee_interactions |
    When I click "Expression" in the row 1 of the "conditionsExpression" datagrid
    Then I should see the popup "Expression"
    And I should see "a & (b | c) & d"
  # Fermeture du popup
    When I click "×"
  # Modification de l'identifiant, saisie correcte
    When I set "condition_composee_interactions_modifiee" for column "ref" of row 1 of the "conditionsExpression" datagrid with a confirmation message
    Then the row 1 of the "conditionsExpression" datagrid should contain:
      | ref                                      |
      | condition_composee_interactions_modifiee |
  # Modification de l'expression, saisie correcte
    When I set "a&b" for column "expression" of row 1 of the "conditionsExpression" datagrid with a confirmation message
    And I click "Expression" in the row 1 of the "conditionsExpression" datagrid
    Then I should see the popup "Expression"
    And I should see "a & b"

  @javascript
  Scenario: Edition of an composed condition for interaction scenario, incorrect input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Conditions composées"
    Then I should see the "conditionsExpression" datagrid
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "conditionsExpression" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "conditionsExpression" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "condition_elementaire_interactions" for column "ref" of row 1 of the "conditionsExpression" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Modification de l'expression, saisie vide
    When I set "" for column "expression" of row 1 of the "conditionsExpression" datagrid
    Then the following message is shown and closed: "L'expression saisie présente les erreurs de syntaxe suivantes :  Il manque un opérateur dans l'expression «  »."
  # Modification de l'expression, saisie invalide
    When I set "a|(b|(c|d)" for column "expression" of row 1 of the "conditionsExpression" datagrid
    Then the following message is shown and closed: "L'expression saisie présente les erreurs de syntaxe suivantes : Au moins une parenthèse ouvrante n'est associée à aucune parenthèse fermante."

  @javascript
  Scenario: Deletion of an elementary condition for interaction scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Conditions composées"
    Then I should see the "conditionsExpression" datagrid
    And the "conditionsExpression" datagrid should contain 1 row
  # Suppression sans obstacle
    When I click "Supprimer" in the row 1 of the "conditionsExpression" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "conditionsExpression" datagrid should contain 0 row