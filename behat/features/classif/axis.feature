@dbFull
Feature: Classification axis feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a classification axis
  # TODO : affichage d'un axe sous la forme : "Libellé (identifiant)"
    Given I am on "classif/axis/manage"
    Then I should see "Axes de classification"
  # Ajout d'un axe, identifiant vide
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I click "Valider"
    Then the field "ref" should have error: "Merci de renseigner ce champ."
  # Ajout d'un axe, identifiant avec des caractères non autorisés
    When I fill in "ref" with "bépo"
    When I click "Valider"
    Then the field "ref" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout d'un axe, saisie correcte
    When I fill in "label" with "À modifier"
    And I fill in "ref" with "a_modifier"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout d'un axe, identifiant déjà utilisé
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "ref" with "a_modifier"
    And I click "Valider"
    Then the field "ref" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Bouton "Annuler"
    When I click "Annuler"
    Then I should not see "Ajout d'un axe"
  # Ajout d'un axe non à la racine
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "label" with "Axe plus grossier que gaz"
    And I fill in "ref" with "axe_plus_grossier_que_gaz"
    And I select "Gaz" from "refParent"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Vérification que l'axe ajouté est bien parent de l'axe Gaz
    When I click "Gaz"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cet axe ne peut pas être supprimé, car il est hiérarchiquement relié à (au moins) un axe plus grossier."

  @javascript
  Scenario: Edition of label and identifier of a classification axis
    Given I am on "classif/axis/manage"
    Then I should see "Axes de classification"
  # Modification "sans effet" d'un axe
    When I wait 4 seconds
    And I click "Gaz"
    Then I should see the popup "Édition d'un axe"
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
  Scenario: Edition of position and parent axis of a classification axis
    Given I am on "classif/axis/manage"
    Then I should see "Axes de classification"
  # Déplacement en dernier (axe situé à la racine)
    When I wait 3 seconds
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
    When I wait 4 seconds
    And I click "Gaz"
    Then I should see the popup "Édition d'un axe"
    When I check "Premier"
    And I click "Confirmer"
    And I wait 2 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement en dernier (axe non situé à la racine)
    When I wait 4 seconds
    And I click "Gaz"
    Then I should see the popup "Édition d'un axe"
    When I check "Dernier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement "après" (axe non situé à la racine)
    When I wait 4 seconds
    And I click "Scope"
    Then I should see the popup "Édition d'un axe"
    When I check "Après"
    And I select "Gaz" from "editAxis_selectAfter"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déplacement à la racine
    When I wait 4 seconds
    And I click "Gaz"
    Then I should see the popup "Édition d'un axe"
    When I select "Aucun" from "editAxis_changeParent"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."

  @javascript
  Scenario:  Deletion of a classification axis
    Given I am on "classif/axis/manage"
    Then I should see "Axes de classification"
  # Axe contenant un membre
    When I wait 4 seconds
    And I click "Gaz"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Pour pouvoir supprimer cet axe, merci de supprimer auparavant ses membres."
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
