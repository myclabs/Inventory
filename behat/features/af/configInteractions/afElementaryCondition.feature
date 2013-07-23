@dbFull
Feature: Elementary condition for interaction feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an elementary condition for interaction scenario
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
    # TODO…
  # Ajout, identifiant correct
    When I fill in "conditionsElementary_ref_addForm" with "test"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 1 of the "conditionsElementary" datagrid should contain:
      | ref  | field         | relation | value |
      | test | Champ booléen | =        |       |

  @javascript
  Scenario: Edition of an elementary condition for interaction scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Conditions élémentaires"
    Then I should see the "conditionsElementary" datagrid

  @javascript
  Scenario: Deletion of an elementary condition for interaction scenario
    Given I am on "af/edit/menu/id/4"
    And I wait for the page to finish loading
    And I open tab "Interactions"
    And I open collapse "Conditions élémentaires"
    Then I should see the "conditionsElementary" datagrid