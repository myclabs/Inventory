@dbFull
Feature: AF numeric expression algo feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an algo numeric expression scenario, correct input
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes numériques"
    And I open collapse "Expressions"
    Then I should see the "algoNumericExpression" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un algorithme numérique de type « expression »"
  # Ajout, saisie valide (au passage on renseigne le libellé)
    When I fill in "algoNumericExpression_label_addForm" with "AAA"
    And I fill in "algoNumericExpression_ref_addForm" with "aaa"
    And I fill in "algoNumericExpression_expression_addForm" with "a/b/c+d-e*f/(g-h)"
    And I fill in "algoNumericExpression_unit_addForm" with "t_co2e.m3^-1"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Algos ordonnés suivant l'ordre de création, vérification des valeurs par défaut
  # TODO : éclaircir cette histoire d'ordre lois
    And the row 1 of the "algoNumericExpression" datagrid should contain:
      | label | ref  | unit          |
      | AAA   | aaa  | t équ. CO2/m³ |
    When I click "Expression" in the row 1 of the "algoNumericExpression" datagrid
    Then I should see the popup "Expression"
    And I should see "(a / (b * c)) + d - (e * f / (g - h))"
    #And I click element "#algoNumericExpression_expression_popup .close:contains('×')"
    And I click "×"

  @javascript
  Scenario: Creation of an algo numeric expression scenario, incorrect input
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes numériques"
    And I open collapse "Expressions"
    Then I should see the "algoNumericExpression" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un algorithme numérique de type « expression »"
  # Ajout, identifiant vide, expression vide, unité vide
    When I click "Valider"
    Then the field "algoNumericExpression_ref_addForm" should have error: "Merci de renseigner ce champ."
    And the field "algoNumericExpression_unit_addForm" should have error: "Merci de saisir un identifiant d'unité valide."
  # Ajout, identifiant avec caractères non autorisés, expression vide, unité invalide
    When I fill in "algoNumericExpression_ref_addForm" with "bépo"
    And I fill in "algoNumericExpression_unit_addForm" with "auie"
    And I click "Valider"
    Then the field "algoNumericExpression_unit_addForm" should have error: "Merci de saisir un identifiant d'unité valide."
  # Ajout, identifiant avec caractères non autorisés, expression vide, unité valide
    When I fill in "algoNumericExpression_unit_addForm" with "t_co2e.m3^-1"
    And I click "Valider"
    And I wait 5 seconds
    Then the field "algoNumericExpression_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé, expression vide, unité valide
    When I fill in "algoNumericExpression_ref_addForm" with "champ_numerique"
    And I click "Valider"
    And I wait 5 seconds
    Then the field "algoNumericExpression_expression_addForm" should have error: "L'expression saisie présente les erreurs de syntaxe suivantes :"
    And the field "algoNumericExpression_expression_addForm" should have error: "Il manque un opérateur dans l'expression «  »."
  # Ajout, identifiant identifiant déjà utilisé, expression invalide, unité valide
    When I fill in "algoNumericExpression_expression_addForm" with "a+(b+(c+d)"
    And I click "Valider"
    Then the field "algoNumericExpression_expression_addForm" should have error: "Au moins une parenthèse ouvrante n'est associée à aucune parenthèse fermante."
  # Ajout, identifiant identifiant déjà utilisé, expression valide, unité valide
    When I fill in "algoNumericExpression_expression_addForm" with "a/b/c+d-e*f/(g-h)"
    And I click "Valider"
    Then the field "algoNumericExpression_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of an algo numeric expression scenario, correct input
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes numériques"
    And I open collapse "Expressions"
    Then I should see the "algoNumericExpression" datagrid
  # Affichage contenu initial
    And the row 1 of the "algoNumericExpression" datagrid should contain:
      | label                | ref                  | unit       |
      | Expression numérique | expression_numerique | t équ. CO2 |
    When I click "Expression" in the row 1 of the "algoNumericExpression" datagrid
    Then I should see the popup "Expression"
    And I should see "champ_numerique * parametre"
    And I click element "#algoNumericExpression_expression_popup .close:contains('×')"
  # Modification du libellé
    When I set "Expression numérique modifiée" for column "label" of row 1 of the "algoNumericExpression" datagrid with a confirmation message
  # Modification de l'identifiant, saisie correcte
    When I set "expression_numerique_modifiee" for column "ref" of row 1 of the "algoNumericExpression" datagrid with a confirmation message
  # Modification de l'expression, saisie correcte
    When I set "a+(b+(c+d))" for column "expression" of row 1 of the "algoNumericExpression" datagrid with a confirmation message
  # Modification de l'unité, saisie correcte
    When I set "m" for column "unit" of row 1 of the "algoNumericExpression" datagrid with a confirmation message
  # Affichage contenu modifié
    And the row 1 of the "algoNumericExpression" datagrid should contain:
      | label                         | ref                           | unit |
      | Expression numérique modifiée | expression_numerique_modifiee | m    |


  @javascript
  Scenario: Edition of an algo numeric expression scenario, incorrect input
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes numériques"
    And I open collapse "Expressions"
    Then I should see the "algoNumericExpression" datagrid
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "algoNumericExpression" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "algoNumericExpression" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "champ_numerique" for column "ref" of row 1 of the "algoNumericExpression" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Modification de l'expression, saisie vide
    When I set "" for column "expression" of row 1 of the "algoNumericExpression" datagrid
    Then the following message is shown and closed: "L'expression saisie présente les erreurs de syntaxe suivantes :  Il manque un opérateur dans l'expression «  »."
  # Modification de l'expression, saisie invalide
    When I set "a+(b+(c+d)" for column "expression" of row 1 of the "algoNumericExpression" datagrid
    Then the following message is shown and closed: "L'expression saisie présente les erreurs de syntaxe suivantes : Au moins une parenthèse ouvrante n'est associée à aucune parenthèse fermante."
  # Modification de l'unité, saisie vide
    When I set "" for column "unit" of row 1 of the "algoNumericExpression" datagrid
    Then the following message is shown and closed: "Merci de saisir un identifiant d'unité valide."
  # Modification de l'unité, saisie invalide
    When I set "auie" for column "unit" of row 1 of the "algoNumericExpression" datagrid
    Then the following message is shown and closed: "Merci de saisir un identifiant d'unité valide."


  @javascript
  Scenario: Deletion of an algo numeric expression scenario
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Algorithmes numériques"
    And I open collapse "Expressions"
    Then I should see the "algoNumericExpression" datagrid
    And the "algoNumericExpression" datagrid should contain 1 row
  # Suppression sans obstacle
    When I click "Supprimer" in the row 1 of the "algoNumericExpression" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "algoNumericExpression" datagrid should contain 0 row
