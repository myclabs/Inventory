@dbFull
Feature: Classification context indicator feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a classification context indicator
    Given I am on "classif/contextindicator/manage"
    Then I should see the "editContextIndicators" datagrid
  # Ajout d'un indicateur contextualisé, Contexte et indicateurs vides
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un indicateur contextualisé"
    When I click "Valider"
    Then the field "editContextIndicators_context_addForm" should have error: "Merci de renseigner ce champ."
    And the field "editContextIndicators_indicator_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout d'un indicateur contextualisé, saisie correcte, axes deux à deux transverses
    When I select "Déplacements" from "editContextIndicators_context_addForm"
    And I select "Chiffre d'affaires" from "editContextIndicators_indicator_addForm"
    And I additionally select "Poste article 75" from "editContextIndicators_axes_addForm"
    And I additionally select "Gaz" from "editContextIndicators_axes_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout d'un indicateur contextualisé déjà existant
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un indicateur contextualisé"
    When I select "Déplacements" from "editContextIndicators_context_addForm"
    And I select "Chiffre d'affaires" from "editContextIndicators_indicator_addForm"
    And I click "Valider"
    Then the field "editContextIndicators_context_addForm" should have error: "Il existe déjà un indicateur contextualisé pour ce contexte et cet indicateur."
    And the field "editContextIndicators_indicator_addForm" should have error: "Il existe déjà un indicateur contextualisé pour ce contexte et cet indicateur."
  # Vérification contenu datagrid
    When I click "Annuler"
    Then the row 4 of the "editContextIndicators" datagrid should contain:
      | context       | indicator         | axes    |
      | Déplacements | Chiffre d'affaires |   |
  # Suppression
    When I click "Supprimer" in the row 1 of the "editContextIndicators" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Ajout d'un indicateur contextualisé, avec axes non deux à deux transverses
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un indicateur contextualisé"
    When I select "Général" from "editContextIndicators_context_addForm"
    And I select "GES" from "editContextIndicators_indicator_addForm"
    And I additionally select "Poste article 75" from "editContextIndicators_axes_addForm"
    And I additionally select "Scope" from "editContextIndicators_axes_addForm"
    And I click "Valider"
    Then the field "editContextIndicators_axes_addForm" should have error: "Merci de sélectionner des axes deux à deux non hiérarchiquement reliés."

  @javascript
  Scenario: Edition of the list of axes of a classification context indicator
    Given I am on "classif/contextindicator/manage"
    Then I should see the "editContextIndicators" datagrid
  # Ajout d'un axe, relié hiérarchiquement à un axe existant
    When I additionally select "Scope" for column "axes" of row 1 of the "editContextIndicators" datagrid
    Then the following message is shown and closed: "Merci de sélectionner des axes deux à deux non hiérarchiquement reliés."
  # Ajout d'un axe, non relié hiérarchiquement à un axe existant
    When I additionally select "Type de déplacement" for column "axes" of row 1 of the "editContextIndicators" datagrid
  # TODO : ajouter dans l'interface un message "Modification effectuée" non présent actuellement.
    Then the row 1 of the "editContextIndicators" datagrid should contain:
      | axes                                       |
      | Gaz, Poste article 75, Type de déplacement |

  @javascript
  Scenario:  Deletion of a classification context indicator
    Given I am on "classif/contextindicator/manage"
    Then I should see the "editContextIndicators" datagrid
    When I click "Supprimer" in the row 1 of the "editContextIndicators" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."