@dbFull
Feature: AF elementary condition for interaction feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an elementary condition for interaction scenario, correct input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Conditions élémentaires"
    Then I should see the "conditionsElementary" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une condition élémentaire"
  # Ajout, identifiant correct
    When I fill in "conditionsElementary_ref_addForm" with "aaa"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Conditions élémentaires affichées dans l'ordre d'ajout
    And the row 4 of the "conditionsElementary" datagrid should contain:
      | ref  | field         | relation | value |
      | aaa  | Champ booléen | =        |       |


  @javascript
  Scenario: Creation of an elementary condition for interaction scenario, incorrect input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Conditions élémentaires"
    Then I should see the "conditionsElementary" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une condition élémentaire"
  # Ajout, sans rien préciser
    When I click "Valider"
    Then the field "conditionsElementary_ref_addForm" should have error: "Merci de renseigner ce champ."
    And the field "conditionsElementary_field_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "conditionsElementary_ref_addForm" with "bépo"
    And I select "Champ booléen" from "conditionsElementary_field_addForm"
    And I click "Valider"
    Then the field "conditionsElementary_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "conditionsElementary_ref_addForm" with "condition_composee_interactions"
    And I click "Valider"
    Then the field "conditionsElementary_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of an elementary condition for interaction scenario, correct input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Conditions élémentaires"
    Then I should see the "conditionsElementary" datagrid
  # Vérification continu initial
    And the row 1 of the "conditionsElementary" datagrid should contain:
      | ref                                 | field                                                                                   | relation | value |
      | condition_elementaire_interactions  | Champ sélection simple utilisé par une condition élémentaire de l'onglet "Interactions" | =        |       |
  # Modification de l'identifiant, saisie correcte
    When I set "condition_elementaire_interactions_modifiee" for column "ref" of row 1 of the "conditionsElementary" datagrid with a confirmation message
  # Popup d'édition
    When I click "Éditer" in the row 1 of the "conditionsElementary" datagrid
    Then I should see the popup "Édition d'une condition élémentaire"
    When I select "≠" from "relation"
    And I select "Option 1" from "Valeur de référence"
    And I click "Enregistrer"
  # TODO : édition en ajax…
  # Édition pas en ajax donc l'onglet est rechargé
    And I open collapse "Conditions élémentaires"
    Then the row 1 of the "conditionsElementary" datagrid should contain:
      | ref                                         | relation | value    |
      | condition_elementaire_interactions_modifiee | ≠        | Option 1 |

  @javascript
  Scenario: Edition of an elementary condition for interaction scenario, incorrect input
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Conditions élémentaires"
    Then I should see the "conditionsElementary" datagrid
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "conditionsElementary" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "conditionsElementary" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "condition_composee_interactions" for column "ref" of row 1 of the "conditionsElementary" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Deletion of an elementary condition for interaction scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Conditions élémentaires"
    Then I should see the "conditionsElementary" datagrid
    And the "conditionsElementary" datagrid should contain 3 row
    And the row 1 of the "conditionsElementary" datagrid should contain:
      | ref                                 |
      | condition_elementaire_interactions  |
    And the row 1 of the "conditionsElementary" datagrid should contain:
      | ref                                 |
      | condition_elementaire_interactions_utilisee_action_setstate  |
    And the row 1 of the "conditionsElementary" datagrid should contain:
      | ref                                 |
      | condition_elementaire_interactions_utilisee_action_setvalue  |
  # Suppression, condition utilisée pour une action de modification de l'état d'un composant
    When I click "Supprimer" in the row 2 of the "conditionsElementary" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cette condition ne peut pas être supprimée, car elle intervient dans le déclenchement d'une (ou plusieurs) actions."
    And the "conditionsElementary" datagrid should contain 3 row
  # Suppression, condition utilisée pour une action de modification de l'état d'un composant
    When I click "Supprimer" in the row 3 of the "conditionsElementary" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cette condition ne peut pas être supprimée, car elle intervient dans le déclenchement d'une (ou plusieurs) actions."
    And the "conditionsElementary" datagrid should contain 3 row
  # Suppression sans obstacle
    When I click "Supprimer" in the row 1 of the "conditionsElementary" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "conditionsElementary" datagrid should contain 2 row