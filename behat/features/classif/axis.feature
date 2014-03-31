@dbFull
Feature: Classification axis feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a classification axis, correct input
    Given I am on "classification/axis/list?library=1"
    And I wait for the page to finish loading
    Then I should see "Axes de classification"
  # TODO : affichage d'un axe sous la forme : "Libellé (identifiant)"
  # Popup d'ajout
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
  # Ajout d'un axe à la racine, saisie correcte
    When I fill in "label" with "Test"
    And I fill in "ref" with "test"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And I should see "Test"
  # Ajout d'un axe non à la racine
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "label" with "Axe plus grossier"
    And I fill in "ref" with "axe_plus_grossier"
    And I select "Axe vide" from "refParent"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Vérification que l'axe ajouté est bien "plus grossier" que l'axe "Axe vide" (a l'axe vide comme parent)
    When I click "Axe vide"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cet axe ne peut pas être supprimé, car il est hiérarchiquement relié à (au moins) un axe plus grossier."

  @javascript @readOnly
  Scenario: Creation of a classification axis, incorrect input
    Given I am on "classification/axis/list?library=1"
    And I wait for the page to finish loading
  # Popup d'ajout
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
  # Ajout, identifiant vide
    When I click "Valider"
    Then the field "ref" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec des caractères non autorisés
    When I fill in "ref" with "bépo"
    When I click "Valider"
    Then the field "ref" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "ref" with "gaz"
    And I click "Valider"
    Then the field "ref" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of label and identifier of a classification axis, correct input
    Given I am on "classification/axis/list?library=1"
    Then I should see "Axes de classification"
  # Modification "sans effet" d'un axe
    And I click "Gaz"
    Then I should see the popup "Édition d'un axe"
  # Modification du libellé et de l'identifiant d'un axe, saisie correcte
    When I fill in "editAxis_label" with "Gaz modifié"
    And I fill in "editAxis_ref" with "gaz_modifie"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Vérification modification et bouton "Annuler"
    When I click "Gaz modifié"
    Then I should see the popup "Édition d'un axe"
    When I click "Annuler"
    Then I should not see "Édition d'un axe"

  @javascript
  Scenario: Edition of label and identifier of a classification axis, incorrect input
    Given I am on "classification/axis/list?library=1"
    Then I should see "Axes de classification"
  # Modification "sans effet" d'un axe
    And I click "Gaz"
    Then I should see the popup "Édition d'un axe"
  # Clic sur "Confirmer" sans avoir effectué aucune modification
    When I click "Confirmer"
    Then the following message is shown and closed: "Cette action n'a entraîné aucune modification."
  # Modification de l'identifiant d'un axe, identifiant vide
    When I click "Gaz"
    Then I should see the popup "Édition d'un axe"
    When I fill in "editAxis_ref" with ""
    And I click "Confirmer"
    Then the field "editAxis_ref" should have error: "Merci de renseigner ce champ."
  # Modification de l'identifiant d'un axe, identifiant avec des caractères non autorisés
    When I fill in "editAxis_ref" with "bépo"
    And I click "Confirmer"
    Then the field "editAxis_ref" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant d'un axe, identifiant déjà utilisé
    When I fill in "editAxis_ref" with "scope"
    And I click "Confirmer"
    Then the field "editAxis_ref" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of position and parent of a classification axis
    Given I am on "classification/axis/list?library=1"
    Then I should see "Axes de classification"
  # Déplacement en dernier (axe situé à la racine)
    And I click "Gaz"
    Then I should see the popup "Édition d'un axe"
    When I check "Dernier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement "après" (axe situé à la racine)
    When I click "Gaz"
    Then I should see the popup "Édition d'un axe"
    When I check "Après"
    And I select "Poste article 75" from "editAxis_selectAfter"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement en premier (axe situé à la racine)
    When I click "Gaz"
    Then I should see the popup "Édition d'un axe"
    When I check "Premier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement comme axe plus grossier d'un autre axe
    When I click "Gaz"
    Then I should see the popup "Édition d'un axe"
    When I select "Poste article 75" from "editAxis_changeParent"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement en premier (axe non situé à la racine)
    And I click "Gaz"
    Then I should see the popup "Édition d'un axe"
    When I check "Premier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement en dernier (axe non situé à la racine)
    And I click "Gaz"
    Then I should see the popup "Édition d'un axe"
    When I check "Dernier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement "après" (axe non situé à la racine)
    And I click "Scope"
    Then I should see the popup "Édition d'un axe"
    When I check "Après"
    And I select "Gaz" from "editAxis_selectAfter"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement à la racine
    And I click "Gaz"
    Then I should see the popup "Édition d'un axe"
    When I select "Aucun" from "editAxis_changeParent"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."

  @javascript
  Scenario:  Deletion of a classification axis
    Given I am on "classification/axis/list?library=1"
    Then I should see "Axes de classification"
  # Axe contenant un élément
    And I click "Gaz"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Pour pouvoir supprimer cet axe, merci de supprimer auparavant ses éléments."
  # Axe relié à un axe plus grossier
    When I click "Poste article 75"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cet axe ne peut pas être supprimé, car il est hiérarchiquement relié à (au moins) un axe plus grossier."
  # Suppression sans obstacle
    When I click "Axe vide"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
