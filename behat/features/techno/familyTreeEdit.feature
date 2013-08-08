@dbFull
Feature: Family tree edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a Techno category
    Given I am on "techno/family/tree-edit"
    And I wait for the page to finish loading
    And I wait 5 seconds
  # Ajout d'une catégorie, libellé vide
    When I click "Ajouter une catégorie"
    Then I should see the popup "Ajout d'une catégorie"
    When I click "Valider"
    Then the field "label" should have error: "Merci de renseigner ce champ."
  # Ajout d'une catégorie, libellé non vide, située à la racine
    When I fill in "label" with "Test catégorie ajoutée à la racine"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout d'une catégorie, libellé non vide, située dans une autre catégorie
  # TODO : permettre l'ajout d'une catégorie dans une autre catégorie.


  @javascript
  Scenario: Edition of a Techno category
    Given I am on "techno/family/tree-edit"
    And I wait 5 seconds
  # Modification du libellé
    When I click "Catégorie vide"
    Then I should see the popup "Édition d'une catégorie"
  # TODO : modification libellé vide
  # When I fill in "familyTree_labelEdit" with ""
  # And I click "Confirmer"
  # Then the field "familyTree_labelEdit" should have error: "Merci de renseigner ce champ."
    When I fill in "familyTree_labelEdit" with "Catégorie vide modifiée"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
    And I should see "Catégorie vide modifiée"
  # Déplacement dans une autre catégorie
    When I click "Catégorie vide modifiée"
    Then I should see the popup "Édition d'une catégorie"
    When I select "Catégorie contenant une sous-catégorie" from "familyTree_changeParent"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement à la racine
  # TODO : homogénéiser libellé racine avec arbre des AFs
    When I wait 3 seconds
    And I click "Catégorie vide modifiée"
    And I select "Racine" from "familyTree_changeParent"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement en premier
    When I click "Catégorie vide modifiée"
    And I check "Premier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement après une autre catégorie
    When I click "Catégorie vide modifiée"
    And I check "Après"
    And I select "Catégorie contenant une sous-catégorie" from "familyTree_selectAfter"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement en dernier
    When I click "Catégorie vide modifiée"
    And I check "Dernier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."


  @javascript
  Scenario: Deletion of a Techno category
    Given I am on "techno/family/tree-edit"
    And I wait 5 seconds
  # Catégorie vide
    When I click "Catégorie vide"
    Then I should see the popup "Édition d'une catégorie"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    And I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And I should not see "Catégorie vide"
  # Catégorie contenant une sous-catégorie
    When I click "Catégorie contenant une sous-catégorie"
    Then I should see the popup "Édition d'une catégorie"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    And I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # TODO : interdire la suppression d'une catégorie contenant une autre catégorie
  # Catégorie contenant un formulaire
    When I click "Catégorie contenant une famille"
    Then I should see the popup "Édition d'une catégorie"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cette catégorie ne peut pas être supprimée, car elle n'est pas vide (elle contient au moins une famille ou une autre catégorie)."

  @javascript
  Scenario: Edition of a family in family tree edit
    Given I am on "techno/family/tree-edit"
    And I wait 5 seconds
  # Modification du libellé, libellé vide
  # TODO : interdire la saisie d'un libellé vide ou bien afficher en même temps l'identifiant ?
    When I click "Combustion de combustible, mesuré en unité de masse"
    And I fill in "familyTree_labelEdit" with ""
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Modification du libellé, libellé non vide
    When I wait 3 seconds
    And I click "Combustion de combustible, mesuré en unité de masse"
    And I fill in "familyTree_labelEdit" with "Combustion (modifiée)"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement dans une autre catégorie
    When I wait 3 seconds
    And I click "Combustion (modifiée)"
    And I select "Catégorie contenant une sous-catégorie" from "familyTree_changeParent"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement en premier
    When I wait 3 seconds
    And I click "Combustion (modifiée)"
    And I check "Premier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement en dernier
    When I wait 3 seconds
    And I click "Combustion (modifiée)"
    And I check "Premier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement après
  # TODO : à faire.

  @javascript
  Scenario: Deletion of a family in family tree edit
    Given I am on "techno/family/tree-edit"
    And I wait 5 seconds
  # Suppression
    When I click "Combustion de combustible, mesuré en unité de masse"
    And I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    And I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Vérification suppression effectuée
    When I wait 5 seconds
    Then I should not see "Combustion de combustible, mesuré en unité de masse"


