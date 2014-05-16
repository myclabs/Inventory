@dbFull
Feature: AF tree edit AF feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Edition of the label of an AF in AF tree edit
    Given I am on "af/library/view/id/1"
  # Modification du libellé, libellé vide
    When I click "Combustion de combustible, mesuré en unité de masse"
    And I fill in "afTree_labelEdit" with ""
    And I click "Confirmer"
    Then the field "afTree_labelEdit" should have error: "Merci de renseigner ce champ."
  # Modification du libellé, libellé non vide
    When I fill in "afTree_labelEdit" with "Combustion (modifiée)"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."

  @javascript
  Scenario: Edition of the position and parent of an AF in AF tree edit
    Given I am on "af/library/view/id/1"
  # Déplacement dans une autre catégorie
    And I click "Combustion de combustible, mesuré en unité de masse"
    And I select "Catégorie contenant une sous-catégorie" from "afTree_changeParent"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement en premier
    And I click "Formulaire test"
    And I check "Premier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement après
    And I click "Formulaire test"
    And I check "Après"
    And I select "Données générales" from "afTree_selectAfter"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement en dernier
    And I click "Formulaire test"
    And I check "Premier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."

  @javascript
  Scenario: Deletion of an AF in AF tree edit, forbidden
    Given I am on "af/library/view/id/1"
  # Tentative de suppression, formulaire associé à des saisies
    When I click "Combustion de combustible, mesuré en unité de masse"
    And I click element "#afTree_editPanel .btn:contains('Supprimer')"
    Then I should see the popup "Demande de confirmation"
    And I click element "#afTree_deletePanel .btn:contains('Confirmer')"
    Then the following message is shown and closed: "Ce formulaire ne peut pas être supprimé car des saisies y sont associées."
  # Tentative de suppression, formulaire associé à des saisies (bis)
    When I click "Données générales"
    And I click element "#afTree_editPanel .btn:contains('Supprimer')"
    Then I should see the popup "Demande de confirmation"
    And I click element "#afTree_deletePanel .btn:contains('Confirmer')"
    Then the following message is shown and closed: "Ce formulaire ne peut pas être supprimé car des saisies y sont associées."
  # Tentative de suppression, formulaire utilisé en tant que sous-formulaire par un autre formulaire
    When I click "Formulaire avec tout type de champ"
    And I click element "#afTree_editPanel .btn:contains('Supprimer')"
    Then I should see the popup "Demande de confirmation"
    And I click element "#afTree_deletePanel .btn:contains('Confirmer')"
    Then the following message is shown and closed: "Ce formulaire ne peut pas être supprimé, car il est appelé en tant que sous-formulaire par un autre formulaire."
  # Tentative de suppression, formulaire utilisé pour la configuration d'une organisation (2)
    When I click "Formulaire avec sous-formulaire répété contenant tout type de champ"
    And I click element "#afTree_editPanel .btn:contains('Supprimer')"
    Then I should see the popup "Demande de confirmation"
    And I click element "#afTree_deletePanel .btn:contains('Confirmer')"
    Then the following message is shown and closed: "Ce formulaire ne peut pas être supprimé car il est utilisé par des organisations."
  # Tentative de suppression, formulaire utilisé pour la configuration d'une organisation
    When I click "Forfait émissions en fonction de la marque"
    And I click element "#afTree_editPanel .btn:contains('Supprimer')"
    Then I should see the popup "Demande de confirmation"
    And I click element "#afTree_deletePanel .btn:contains('Confirmer')"
    Then the following message is shown and closed: "Ce formulaire ne peut pas être supprimé car il est utilisé par des organisations."
  # Vérification suppression non effectuée
    And I should see "Combustion de combustible, mesuré en unité de masse"
    And I should see "Données générales"
    And I should see "Formulaire avec tout type de champ"
    And I should see "Formulaire avec sous-formulaire répété contenant tout type de champ"
    And I should see "Forfait émissions en fonction de la marque"

  @javascript
  Scenario: Deletion of an AF in AF tree edit, success
    #6193 Dans le jeu de données "full.sql", impossible de supprimer le formulaire "Formulaire test"
    Given I am on "af/library/view/id/1"
  # Suppression sans obstacle, formulaire vide
    When I click "Formulaire vide"
    And I click element "#afTree_editPanel .btn:contains('Supprimer')"
    Then I should see the popup "Demande de confirmation"
    And I click element "#afTree_deletePanel .btn:contains('Confirmer')"
    Then the following message is shown and closed: "Suppression effectuée."
  # Suppression sans obstacle, "Formulaire test"
    When I click "Formulaire test"
    And I click element "#afTree_editPanel .btn:contains('Supprimer')"
    Then I should see the popup "Demande de confirmation"
    And I click element "#afTree_deletePanel .btn:contains('Confirmer')"
    Then the following message is shown and closed: "Suppression effectuée."
  # Suppression sans obstacle, "Formulaire avec sous-formulaires"
    When I click "Formulaire avec sous-formulaires"
    And I click element "#afTree_editPanel .btn:contains('Supprimer')"
    Then I should see the popup "Demande de confirmation"
    And I click element "#afTree_deletePanel .btn:contains('Confirmer')"
    Then the following message is shown and closed: "Suppression effectuée."

  @javascript @readOnly
  Scenario: Link towards configuration view, from AF tree edit
    Given I am on "af/library/view/id/1"
    When I click "Combustion de combustible, mesuré en unité de masse"
    And I click element "#afTree_editPanel a:contains('Configuration')"
  # Vérification qu'on est bien sur la page "Configuration"
    And I open tab "Contrôle"
    Then I should see "Combustion de combustible, mesuré en unité de masse"

  @javascript @readOnly
  Scenario: Link towards test view, from AF tree edit
    Given I am on "af/library/view/id/1"
    When I click "Combustion de combustible, mesuré en unité de masse"
    And I click element "#afTree_editPanel a:contains('Test')"
  # Vérification qu'on est bien sur la page "Test"
    Then I should see "Nature du combustible"
