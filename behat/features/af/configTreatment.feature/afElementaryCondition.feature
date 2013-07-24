@dbFull
Feature: AF elementary condition for treatment feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an elementary condition for treatment scenario
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Conditions"
    And I open collapse "Conditions élémentaires"
    Then I should see the "algoConditionElementary" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une condition élémentaire"
  # Ajout, identifiant vide
    When I click "Valider"
    Then the field "algoConditionElementary_ref_addForm" should have error: "Merci de renseigner ce champ."
    And the field "algoConditionElementary_input_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, champ non vide, identifiant avec caractères non autorisés
    When I select "Champ numérique" from "algoConditionElementary_input_addForm"
    And I fill in "algoConditionElementary_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "algoConditionElementary_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "algoConditionElementary_ref_addForm" with "champ_numerique"
    And I click "Valider"
    Then the field "algoConditionElementary_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Ajout, saisie correcte
    When I fill in "algoConditionElementary_ref_addForm" with "aaa"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Conditions élémentaires ordonnées suivant l'ordre de création
    And the row 2 of the "algoNumericConstant" datagrid should contain:
      | ref | input           |
      | aaa | Champ numérique |

  @javascript
  Scenario: Edition of an elementary condition for treatment scenario
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Conditions"
    And I open collapse "Conditions élémentaires" 
    Then I should see the "algoConditionElementary" datagrid
    And the row 1 of the "algoConditionElementary" datagrid should contain:
      | ref                   | input                                                                                 |
      | condition_elementaire | Champ sélection simple utilisé par une condition élémentaire de l'onglet "Traitement" |
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "algoConditionElementary" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "algoConditionElementary" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "champ_numerique" for column "ref" of row 1 of the "algoConditionElementary" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Modification de l'identifiant, saisie correcte
    When I set "condition_elementaire_modifiee" for column "ref" of row 1 of the "algoConditionElementary" datagrid with a confirmation message
  # Popup d'édition
    When I click "Éditer" in the row 1 of the "algoConditionElementary" datagrid
    Then I should see the popup "Édition d'une condition élémentaire"
    When I select "≠" from "relation"
    And I select "option_1" from "Valeur de référence"
    And I click "Enregistrer"
  # TODO : édition en ajax…
  # Édition pas en ajax donc l'onglet est rechargé
    And I open collapse "Conditions"
    And I open collapse "Conditions élémentaires"
    Then the row 1 of the "algoConditionElementary" datagrid should contain:
      | ref                            | relation | value    |
      | condition_elementaire_modifiee | ≠        | option_1 |

  @javascript
  Scenario: Deletion of an elementary condition for treatment scenario
    Given I am on "af/edit/menu/id/4/onglet/traitement"
    And I wait for the page to finish loading
    And I open collapse "Conditions"
    And I open collapse "Conditions élémentaires"
    Then I should see the "algoConditionElementary" datagrid
    And the "algoConditionElementary" datagrid should contain 1 row
  # Suppression sans obstacle
    When I click "Supprimer" in the row 1 of the "algoConditionElementary" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "algoConditionElementary" datagrid should contain 0 row

