@dbFull
Feature: Organizational axis feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an organizational axis, correct input
  # Accès à l'onglet "Axes"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Axes"
    And I wait 5 seconds
    Then I should see "Année (annee)"
  # Popup d'ajout
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
  # Ajout d'un axe à la racine, saisie correcte
    When I fill in "addAxis_label" with "Test"
    And I fill in "addAxis_ref" with "test"
    And I click "Valider"
    And I wait 5 seconds
    Then the following message is shown and closed: "Ajout effectué."
    And I should see "Test"
  # Ajout d'un axe non à la racine
    When I wait 5 seconds
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Axe plus grossier que l'axe vide"
    And I fill in "addAxis_ref" with "axe_plus_grossier_que_axe_vide"
    And I select "Axe vide" from "addAxis_parent"
    And I click "Valider"
    And I wait 5 seconds
    Then the following message is shown and closed: "Ajout effectué."
  # Vérification que l'axe ajouté est bien parent de l'axe "axe vide"
    When I click "Axe vide"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cet axe ne peut pas être supprimé, car il est hiérarchiquement relié à (au moins) un axe plus grossier."

  @javascript
  Scenario: Creation of an organizational axis, incorrect input
  # Accès à l'onglet "Axes"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Axes"
    And I wait 5 seconds
  # TODO : modification sans effet
  # Popup d'ajout
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
  # Ajout, identifiant vide
    When I click "Valider"
    Then the field "addAxis_ref" should have error: "Merci de renseigner ce champ."
  # Ajout d'un axe, identifiant avec des caractères non autorisés
    When I fill in "addAxis_ref" with "bépo"
    When I click "Valider"
    Then the field "addAxis_ref" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout d'un axe, identifiant déjà utilisé
    When I fill in "addAxis_ref" with "annee"
    And I click "Valider"
    Then the field "addAxis_ref" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Bouton "Annuler"
    When I click "Annuler"
    Then I should not see "Ajout d'un axe"

  @javascript
  Scenario: Edition of label and identifier of an organizational axis
  # Accès à l'onglet "Axes"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Axes"
    And I wait 3 seconds
  # Modification du libellé et de l'identifiant d'un axe
    When I click "Site"
    Then I should see the popup "Édition d'un axe"
    When I fill in "editAxis_label" with "Site modifié"
    And I fill in "editAxis_ref" with "site_modifie"
    And I click "Confirmer"
    And I wait 10 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Modification de l'identifiant d'un axe, identifiant vide
    When I click "Site modifié"
    Then I should see the popup "Édition d'un axe"
    When I fill in "editAxis_ref" with ""
    And I click "Confirmer"
    Then the field "editAxis_ref" should have error: "Merci de renseigner ce champ."
  # Modification de l'identifiant d'un axe, identifiant avec des caractères non autorisés
    When I fill in "editAxis_ref" with "bépo"
    And I click "Confirmer"
    Then the field "editAxis_ref" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Bouton "Annuler"
    When I click "Annuler"
    Then I should not see "Édition d'un axe"

  @javascript
  Scenario: Edition of position and parent axis of an organizational axis
  # Accès à l'onglet "Axes"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Axes"
    And I wait 5 seconds
  # Déplacement en premier (axe situé à la racine)
    When I click "Catégorie"
    Then I should see the popup "Édition d'un axe"
    When I check "Premier"
    And I click "Confirmer"
    And I wait 5 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement "Après" (axe situé à la racine)
    When I click "Catégorie"
    Then I should see the popup "Édition d'un axe"
    When I check "Après"
    And I select "Année" from "editAxis_selectAfter"
    And I click "Confirmer"
    And I wait 5 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement en dernier (axe situé à la racine)
    When I click "Catégorie"
    Then I should see the popup "Édition d'un axe"
    When I check "Dernier"
    And I click "Confirmer"
    And I wait 5 seconds
    Then the following message is shown and closed: "Modification effectuée."

  @javascript
  Scenario:  Deletion of an organizational axis
  # Accès à l'onglet "Axes"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Axes"
    And I wait 5 seconds
  # Axe contenant un élément
    When I click "Pays"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Pour pouvoir supprimer cet axe, merci de supprimer auparavant ses éléments."
  # Axe utilisé dans la définition d'une granularité
    When I click "Année"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cet axe ne peut pas être supprimé, car il intervient dans la définition de (au moins) un niveau organisationnel."
  # 
  # Remarque : axe associé à un axe parent : déjà testé dans le scénario "Creation"
  #
  # Suppression sans obstacle
    When I click "Axe vide"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    And I wait 5 seconds
    Then the following message is shown and closed: "Suppression effectuée."
    And I should not see "Axe vide"














