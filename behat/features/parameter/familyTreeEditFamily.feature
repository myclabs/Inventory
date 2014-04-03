@dbFull
Feature: Family tree edit of families feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Edit family label in family tree edit
    Given I am on "parameter/library/view/id/1"
  # Modification du libellé, libellé vide
    When I click "Combustion de combustible, mesuré en unité de masse"
    And I fill in "familyTree_labelEdit" with ""
    And I click "Confirmer"
    Then the field "familyTree_labelEdit" should have error: "Merci de renseigner ce champ."
  # Modification du libellé, libellé non vide
    When I fill in "familyTree_labelEdit" with "Combustion (modifiée)"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
    Then I should see "Combustion (modifiée)"

  @javascript
  Scenario: Edition of position and parent of a family in family tree edit
    Given I am on "parameter/library/view/id/1"
  # Déplacement en dernier
    And I click "Combustion de combustible, mesuré en unité de masse"
    And I check "Premier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement après
    And I click "Combustion de combustible, mesuré en unité de masse"
    And I check "Après"
    And I select "Masse volumique de combustible" from "familyTree_selectAfter"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement en premier
    And I click "Combustion de combustible, mesuré en unité de masse"
    And I check "Premier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement dans une autre catégorie
    And I click "Combustion de combustible, mesuré en unité de masse"
    And I select "Catégorie contenant une sous-catégorie" from "familyTree_changeParent"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # TODO : autoriser le déplacement à la racine

  @javascript
  Scenario: Deletion of a family in family tree edit
    Given I am on "parameter/library/view/id/1"
  # Suppression
    When I click "Combustion de combustible, mesuré en unité de masse"
    And I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    And I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Vérification suppression effectuée
    Then I should not see "Combustion de combustible, mesuré en unité de masse"
