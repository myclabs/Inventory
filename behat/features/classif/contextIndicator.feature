@dbFull
Feature: Classification context indicator feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a classification context indicator
    When I am on "classif/contextindicator/manage"
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
  Scenario:  Deletion of a classification context indicator
    When I am on "classif/contextindicator/manage"
    Then I should see the "editContextIndicators" datagrid
    When I click "Supprimer" in the row 1 of the "editContextIndicators" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."