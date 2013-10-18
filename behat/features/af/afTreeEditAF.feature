@dbFull
Feature: AF tree edit AF feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Edition of the label of an AF in AF tree edit
    Given I am on "af/af/tree"
    And I wait 7 seconds
  # Modification du libellé, libellé vide
    When I click "Combustion de combustible, mesuré en unité de masse"
    And I fill in "afTree_labelEdit" with ""
    And I click "Confirmer"
    Then the field "afTree_labelEdit" should have error: "Merci de renseigner ce champ."
  # Modification du libellé, libellé non vide
    When I fill in "afTree_labelEdit" with "Combustion (modifiée)"
    And I click "Confirmer"
    And I wait 5 seconds
    Then the following message is shown and closed: "Modification effectuée."

  @javascript
  Scenario: Edition of the position and parent of an AF in AF tree edit
    Given I am on "af/af/tree"
    And I wait 7 seconds
  # Déplacement dans une autre catégorie
    And I click "Combustion de combustible, mesuré en unité de masse"
    And I select "Catégorie contenant une sous-catégorie" from "afTree_changeParent"
    And I click "Confirmer"
    And I wait 5 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement en premier
    And I click "Formulaire test"
    And I check "Premier"
    And I click "Confirmer"
    And I wait 5 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement après
    And I click "Formulaire test"
    And I check "Après"
    And I select "Données générales" from "afTree_selectAfter"
    And I click "Confirmer"
    And I wait 5 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement en dernier
    And I click "Formulaire test"
    And I check "Premier"
    And I click "Confirmer"
    And I wait 5 seconds
    Then the following message is shown and closed: "Modification effectuée."

  @javascript
  Scenario: Deletion of an AF in AF tree edit, forbidden
    Given I am on "af/af/tree"
    And I wait 7 seconds
  # Suppression, formulaire utilisé comme sous-formulaire (non répété)
    When I click "Données générales"
    And I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    And I click "Confirmer"
    Then the following message is shown and closed: "Ce formulaire ne peut pas être supprimé, car il est appelé en tant que sous-formulaire par un autre formulaire."
  # Suppression, formulaire utilisé comme sous-formulaire (répété)
    When I click "Combustion de combustible, mesuré en unité de masse"
    And I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    And I click "Confirmer"
    Then the following message is shown and closed: "Ce formulaire ne peut pas être supprimé, car il est appelé en tant que sous-formulaire par un autre formulaire."

  @javascript
  Scenario: Deletion of an AF in AF tree edit, authorized
    #6193 	Dans le jeu de données "full.sql", impossible de supprimer le formulaire "Formulaire test"
    Given I am on "af/af/tree"
    And I wait 7 seconds
  # Suppression sans obstacle, formulaire vide
    When I click "Formulaire vide"
    And I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    And I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Suppression sans obstacle, "Formulaire test"
    When I click "Formulaire test"
    And I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    And I click "Confirmer"
    Then the following message is shown and closed: "Ce formulaire ne peut pas être supprimé car il est utilisé par des organisations."
  # Suppression sans obstacle, "Formulaire avec sous-formulaires"
    When I click "Formulaire avec sous-formulaires"
    And I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    And I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Suppression sans obstacle, "Données générales"
    When I click "Données générales"
    And I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    And I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Suppression sans obstacle, "Combustion de combustible, mesuré en unité de masse"
    When I click "Combustion de combustible, mesuré en unité de masse"
    And I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    And I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Vérification suppression effectuée
    When I wait 5 seconds
    Then I should see "Données générales"
    And I should not see "Combustion de combustible, mesuré en unité de masse"
    And I should not see "Données générales"
    And I should not see "Formulaire avec sous-formulaires"
    And I should not see "Formulaire test"
    And I should not see "Formulaire vide"

  @javascript
  Scenario: Link towards configuration view, from AF tree edit
    Given I am on "af/af/tree"
    And I wait 7 seconds
    When I click "Combustion de combustible, mesuré en unité de masse"
    And I click "Configuration"
  # Vérification qu'on est bien sur la page "Configuration"
    And I open tab "Contrôle"
    Then I should see "Combustion de combustible, mesuré en unité de masse"

  @javascript
  Scenario: Link towards test view, from AF tree edit
    Given I am on "af/af/tree"
    And I wait 7 seconds
    When I click "Combustion de combustible, mesuré en unité de masse"
    And I click "Test"
  # Vérification qu'on est bien sur la page "Test"
    Then I should see "Nature du combustible"