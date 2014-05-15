@dbFull
Feature: AF selection expression algo feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an algo selection expression scenario, correct input
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes de sélection d’identifiant"
    And I open collapse "Expressions"
    Then I should see the "algoSelectionTextkeyExpression" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un algorithme de sélection d’identifiant de type expression"
  # TODO : rajouter guillemets (pas réussi à traiter l'échappement).
  # Ajout, saisie correcte
    When I fill in "algoSelectionTextkeyExpression_ref_addForm" with "aaa"
    And I fill in "algoSelectionTextkeyExpression_expression_addForm" with "a:(b:(c:d;e:(f:g;:h)))"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Algos ordonnés suivant l'ordre de création
    And the row 1 of the "algoSelectionTextkeyExpression" datagrid should contain:
      | ref  |
      | aaa  |
    When I click "Expression" in the row 1 of the "algoSelectionTextkeyExpression" datagrid
    Then I should see the popup "Expression"
    And I should see "a : (b : (c : d ; e : (f : g ; : h)))"

  @javascript
  Scenario: Creation of an algo selection expression scenario, incorrect input
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes de sélection d’identifiant"
    And I open collapse "Expressions"
    Then I should see the "algoSelectionTextkeyExpression" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un algorithme de sélection d’identifiant de type expression"
  # TODO : rajouter guillemets (pas réussi à traiter l'échappement).
  # Ajout, identifiant vide
    When I click "Valider"
    Then the field "algoSelectionTextkeyExpression_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "algoSelectionTextkeyExpression_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "algoSelectionTextkeyExpression_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé, expression vide
    When I fill in "algoSelectionTextkeyExpression_ref_addForm" with "c_n"
    And I click "Valider"
    Then the field "algoSelectionTextkeyExpression_expression_addForm" should have error: "L'expression saisie présente les erreurs de syntaxe suivantes :"
    And the field "algoSelectionTextkeyExpression_expression_addForm" should have error: "Il manque un opérateur dans l'expression «  »."
  # Ajout, identifiant déjà utilisé, expression invalide
    When I fill in "algoSelectionTextkeyExpression_expression_addForm" with "a:(b:(c:d)"
    And I click "Valider"
    Then the field "algoSelectionTextkeyExpression_expression_addForm" should have error: "Au moins une parenthèse ouvrante n'est associée à aucune parenthèse fermante."
  # Ajout, identifiant déjà utilisé, expression valide
    When I fill in "algoSelectionTextkeyExpression_expression_addForm" with "a:(b:(c:d;e:(f:g;:h)))"
    And I click "Valider"
    Then the field "algoSelectionTextkeyExpression_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of an algo selection expression scenario, correct input
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes de sélection d’identifiant"
    And I open collapse "Expressions"
    Then I should see the "algoSelectionTextkeyExpression" datagrid
  # Modification de l'identifiant, saisie correcte
    When I set "expression_sel_modifiee" for column "ref" of row 1 of the "algoSelectionTextkeyExpression" datagrid with a confirmation message
  # Attention, modification de l'ordre lors de l'édition (???)
    Then the "algoSelectionTextkeyExpression" datagrid should contain a row:
      | ref                     |
      | expression_sel_modifiee |
  # Modification de l'expression, saisie correcte
    When I set "a:b" for column "expression" of row 1 of the "algoSelectionTextkeyExpression" datagrid with a confirmation message
    When I click "Expression" in the row 2 of the "algoSelectionTextkeyExpression" datagrid
    Then I should see the popup "Expression"
    And I should see "a : b"

  @javascript
  Scenario: Edition of an algo selection expression scenario, incorrect input
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes de sélection d’identifiant"
    And I open collapse "Expressions"
    Then I should see the "algoSelectionTextkeyExpression" datagrid
  # Affichage contenu initial
    And the row 1 of the "algoSelectionTextkeyExpression" datagrid should contain:
      | ref                  |
      | expression_sel |
    When I click "Expression" in the row 1 of the "algoSelectionTextkeyExpression" datagrid
    Then I should see the popup "Expression"
    And I should see "a : (b : (c : d ; e : (f : g ; : h)))"
    When I click "×"
  # Modification de l'identifiant, identifiant vide
    And I set "" for column "ref" of row 1 of the "algoSelectionTextkeyExpression" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "algoSelectionTextkeyExpression" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "c_n" for column "ref" of row 1 of the "algoSelectionTextkeyExpression" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Modification de l'expression, saisie vide
    When I set "" for column "expression" of row 1 of the "algoSelectionTextkeyExpression" datagrid
    Then the following message is shown and closed: "L'expression saisie présente les erreurs de syntaxe suivantes : Il manque un opérateur dans l'expression"
  # Modification de l'expression, saisie invalide
    When I set "a:(b:(c:d)" for column "expression" of row 1 of the "algoSelectionTextkeyExpression" datagrid
    Then the following message is shown and closed: "L'expression saisie présente les erreurs de syntaxe suivantes : Au moins une parenthèse ouvrante n'est associée à aucune parenthèse fermante."

  @javascript
  Scenario: Deletion of an algo selection expression scenario
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes de sélection d’identifiant"
    And I open collapse "Expressions"
    Then I should see the "algoSelectionTextkeyExpression" datagrid
    And the "algoSelectionTextkeyExpression" datagrid should contain 3 row
    And the row 1 of the "algoSelectionTextkeyExpression" datagrid should contain:
      | ref  |
      | expression_sel |
    And the row 2 of the "algoSelectionTextkeyExpression" datagrid should contain:
      | ref  |
      | expression_sel_coord_param |
    And the row 3 of the "algoSelectionTextkeyExpression" datagrid should contain:
      | ref  |
      | expression_sel_index_algo |
  # Algo utilisé pour la détermination d'une coordonnée de paramètre
    When I click "Supprimer" in the row 2 of the "algoSelectionTextkeyExpression" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cet algorithme ne peut pas être supprimé, car il est utilisé pour l'indexation d'au moins un algorithme numérique ou la détermination d'au moins une coordonnée d'algorithme de type paramètre."
    And the "algoSelectionTextkeyExpression" datagrid should contain 3 row
  # Algo utilisé pour l'indexation d'un algo numérique
    When I click "Supprimer" in the row 3 of the "algoSelectionTextkeyExpression" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cet algorithme ne peut pas être supprimé, car il est utilisé pour l'indexation d'au moins un algorithme numérique ou la détermination d'au moins une coordonnée d'algorithme de type paramètre."
    And the "algoSelectionTextkeyExpression" datagrid should contain 3 row
  # Suppression sans obstacle
    When I click "Supprimer" in the row 1 of the "algoSelectionTextkeyExpression" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "algoSelectionTextkeyExpression" datagrid should contain 2 row
