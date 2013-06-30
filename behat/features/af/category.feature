@dbEmpty
Feature: afCategory

  Background:
    Given I am logged in

  @javascript
  Scenario: afCategory1
    Given I am on "af/af/tree"
  # Ajout d'une catégorie, libellé vide
    When I click "Ajouter une catégorie"
    Then I should see the popup "Ajout d'une catégorie"
    When I click "Valider"
    Then the field "label" should have error: "Merci de renseigner ce champ."
  # Ajout d'une catégorie, libellé vide
    When I fill in "label" with "À supprimer"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Modification du libellé d'une catégorie
    When I click "À supprimer"
    Then I should see the popup "Édition d'une catégorie"
    When I fill in "afTree_labelEdit" with "À supprimer tout de suite"
    And I click element "#afTree_editPanel button:contains('Confirmer')"
    Then the following message is shown and closed: "Modification effectuée."
  # Suppression d'une catégorie
    When I click "À supprimer tout de suite"
    Then I should see the popup "Édition d'une catégorie"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click element "#afTree_deletePanel button:contains('Confirmer')"
    Then the following message is shown and closed: "Suppression effectuée."

  @javascript
  Scenario: afCategory2
    Given I am on "af/af/tree"
  # Ajout catégorie 1
    When I click "Ajouter une catégorie"
    Then I should see the popup "Ajout d'une catégorie"
    When I fill in "label" with "Catégorie 1"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout catégorie 2
    When I click "Ajouter une catégorie"
    And I fill in "label" with "Catégorie 2"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Déplacement catégorie 1 dans catégorie 2
    When I click "Catégorie 1"
    And I select "Catégorie 2" from "afTree_changeParent"
    And I click element "#afTree_editPanel button:contains('Confirmer')"
    Then the following message is shown and closed: "Modification effectuée."
  # Tentative de suppression catégorie 1
    # When I click "Catégorie 2"
    # And I click "Supprimer"
    # Then I should see the popup "Demande de confirmation"
    # When I click element "#afTree_deletePanel button:contains('Confirmer')"
    # Then the following message is shown and closed: "Suppression effectuée."
    # TODO : interdire la suppression d'une catégorie contenant une autre catégorie
# Déplacement catégorie 1 à la racine
    When I wait 3 seconds
    And I click "Catégorie 1"
    And I select "Aucun" from "afTree_changeParent"
    And I click element "#afTree_editPanel button:contains('Confirmer')"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement catégorie 1 en premier
    When I click "Catégorie 1"
    And I check "Premier"
    And I click element "#afTree_editPanel button:contains('Confirmer')"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement catégorie 1 après catégorie 2
    When I click "Catégorie 1"
    And I check "Après"
    And I select "Catégorie 2" from "afTree_selectAfter"
    And I click element "#afTree_editPanel button:contains('Confirmer')"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement catégorie 2 en dernier
    When I click "Catégorie 2"
    And I check "Dernier"
    And I click element "#afTree_editPanel button:contains('Confirmer')"
    Then the following message is shown and closed: "Modification effectuée."

