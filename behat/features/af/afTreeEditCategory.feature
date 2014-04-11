@dbFull
Feature: AF tree edit category feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an AF category
    Given I am on "af/library/view/id/1"
    And I wait for the page to finish loading
  # Ajout d'une catégorie, libellé vide
    When I click "Ajouter une catégorie"
    Then I should see the popup "Ajout d'une catégorie"
    When I click "Valider"
    Then the field "label" should have error: "Merci de renseigner ce champ."
  # Ajout d'une catégorie, libellé non vide
    When I fill in "label" with "Test"
    And I click "Valider"
    And I wait 7 seconds
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout d'une catégorie, libellé non vide, située dans une autre catégorie
  # TODO : permettre l'ajout d'une catégorie dans une autre catégorie.

  @javascript
  Scenario: Edition of label of an AF category
    Given I am on "af/library/view/id/1"
  # Ouverture popup modification
    When I click "Catégorie vide"
    Then I should see the popup "Édition d'une catégorie"
  # Modification du libellé, libellé vide
    When I fill in "afTree_labelEdit" with ""
    And I click "Confirmer"
    Then the field "afTree_labelEdit" should have error: "Merci de renseigner ce champ."
  # Modification du libellé, saisie correcte
    When I fill in "afTree_labelEdit" with "Catégorie vide modifiée"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
    And I should see "Catégorie vide modifiée"

  @javascript
  Scenario: Edition of position an parent of an AF category
    Given I am on "af/library/view/id/1"
  # Déplacement dans une autre catégorie
    When I click "Catégorie vide"
    Then I should see the popup "Édition d'une catégorie"
    When I select "Catégorie contenant une sous-catégorie" from "afTree_changeParent"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement à la racine
    And I click "Catégorie vide"
    And I select "Aucun" from "afTree_changeParent"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement en premier
    And I click "Catégorie vide"
    And I check "Premier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement après une autre catégorie
    And I click "Catégorie vide"
    And I check "Après"
    And I select "Catégorie contenant une sous-catégorie" from "afTree_selectAfter"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement en dernier
    And I click "Catégorie vide"
    And I check "Dernier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."

  @javascript
  Scenario:  Deletion of an AF category
    Given I am on "af/library/view/id/1"
  # Catégorie contenant une sous-catégorie
    And I click "Catégorie contenant une sous-catégorie"
    Then I should see the popup "Édition d'une catégorie"
    And I click element "#afTree_editPanel .btn:contains('Supprimer')"
    Then I should see the popup "Demande de confirmation"
    And I click element "#afTree_deletePanel .btn:contains('Confirmer')"
    And I click element "#afTree_editPanel .btn:contains('Annuler')"
    Then the following message is shown and closed: "Cette catégorie ne peut pas être supprimée, car elle n'est pas vide (elle contient au moins un formulaire ou une autre catégorie)."
    And I should see "Catégorie contenant une sous-catégorie"
  # Catégorie contenant un formulaire
    When I click "Catégorie contenant un formulaire"
    Then I should see the popup "Édition d'une catégorie"
    And I click element "#afTree_editPanel .btn:contains('Supprimer')"
    Then I should see the popup "Demande de confirmation"
    And I click element "#afTree_deletePanel .btn:contains('Confirmer')"
    And I click element "#afTree_editPanel .btn:contains('Annuler')"
    Then the following message is shown and closed: "Cette catégorie ne peut pas être supprimée, car elle n'est pas vide (elle contient au moins un formulaire ou une autre catégorie)."
    And I should see "Catégorie contenant un formulaire"
  # Catégorie vide
    When I click "Catégorie vide"
    Then I should see the popup "Édition d'une catégorie"
    And I click element "#afTree_editPanel .btn:contains('Supprimer')"
    Then I should see the popup "Demande de confirmation"
    And I click element "#afTree_deletePanel .btn:contains('Confirmer')"
    Then the following message is shown and closed: "Suppression effectuée."
    And I should not see "Catégorie vide"
