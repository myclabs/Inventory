@dbFull
Feature: AF composed condition for treatment feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of composed condition for treatment scenario
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Conditions"
    And I open collapse "Conditions composées"
    Then I should see the "algoConditionExpression" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une condition composée"
  # Ajout, identifiant vide, expression vide
    When I click "Valider"
    Then the field "algoConditionExpression_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés, expression vide
    When I fill in "algoConditionExpression_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "algoConditionExpression_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé, expression vide
    When I fill in "algoConditionExpression_ref_addForm" with "champ_numerique"
    And I click "Valider"
    Then the field "algoConditionExpression_expression_addForm" should have error: "Il manque un opérateur dans l'expression « »."
  # Ajout, identifiant déjà utilisé, expression invalide
    When I fill in "algoConditionExpression_expression_addForm" with "a|(b|(c|d)))"
    And I click "Valider"
    Then the field "algoConditionExpression_expression_addForm" should have error: "Au moins une parenthèse fermante n'est associée à aucune parenthèse ouvrante."
  # Ajout, identifiant déjà utilisé, expression valide
    When I fill in "algoConditionExpression_expression_addForm" with "a & (b | c) & d"
    And I click "Valider"
    Then the field "algoConditionExpression_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
    # Ajout, identifiant valide, expression valide
    When I fill in "algoConditionExpression_ref_addForm" with "bépo"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Conditions composées ordonnées suivant l'ordre de création
    And the row 2 of the "algoConditionExpression" datagrid should contain:
      | ref |
      | aaa |
    When I click "Expression" in the row 2 of the "algoConditionExpression" datagrid
    Then I should see the popup "Expression"
    And I should see "a & (b | c) & d"

  @javascript
  Scenario: Edition of composed condition for treatment scenario
  # Accès au datagrid
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Conditions"
    And I open collapse "Conditions composées"
    Then I should see the "algoConditionExpression" datagrid
  # Vérification contenu initial
    And the row 1 of the "algoConditionExpression" datagrid should contain:
      | ref |
      | condition_composee |
    When I click "Expression" in the row 1 of the "algoConditionExpression" datagrid
    Then I should see the popup "Expression"
    And I should see "condition_elementaire | condition_inexistante"
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "algoConditionExpression" datagrid
    And I wait 2 seconds
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "algoConditionExpression" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "champ_numerique" for column "ref" of row 1 of the "algoConditionExpression" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Modification de l'identifiant, saisie correcte
    When I set "condition_composee_modifiee" for column "ref" of row 1 of the "algoConditionExpression" datagrid with a confirmation message
    Then the row 1 of the "algoConditionExpression" datagrid should contain:
      | ref |
      | condition_composee_modifiee |
  # Modification de l'expression, saisie vide
    When I set "" for column "expression" of row 1 of the "algoConditionExpression" datagrid
    Then the following message is shown and closed: "L'expression saisie présente les erreurs de syntaxe suivantes : Il manque un opérateur dans l'expression « »."
  # Modification de l'expression, saisie invalide
    When I set "a|(b|(c|d)" for column "expression" of row 1 of the "algoConditionExpression" datagrid
    Then the following message is shown and closed: "L'expression saisie présente les erreurs de syntaxe suivantes : Au moins une parenthèse ouvrante n'est associée à aucune parenthèse fermante."
  # Modification de l'expression, saisie correcte
    When I set "a&(b|c)&d" for column "expression" of row 1 of the "algoNumericExpression" datagrid with a confirmation message
    And I click "Expression" in the row 1 of the "algoConditionExpression" datagrid
    Then I should see the popup "Expression"
    And I should see "a & (b | c) & d"

  @javascript
  Scenario: Deletion of composed condition for treatment scenario
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Conditions"
    And I open collapse "Conditions composées"
    Then I should see the "algoConditionExpression" datagrid
    And the "algoConditionElementary" datagrid should contain 1 row
  # Suppression sans obstacle
    When I click "Supprimer" in the row 1 of the "algoConditionExpression" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "algoConditionExpression" datagrid should contain 0 row