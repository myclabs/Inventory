@dbWithClassifAxesIndicatorsContexts
Feature: classifContextIndicator

  Background:
    Given I am logged in

  @javascript
  Scenario: classifContextIndicator1
    When I am on "classif/contextindicator/manage"
    Then I should see the "editContextIndicators" datagrid
  # Ajout d'un indicateur contextualisé, Contexte et indicateurs vides
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un indicateur contextualisé"
    When I click "Valider"
    Then the field "editContextIndicators_context_addForm" should have error: "Merci de renseigner ce champ."
    And the field "editContextIndicators_indicator_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout d'un indicateur contextualisé, sans axe
    When I select "Général" from "editContextIndicators_context_addForm"
    And I select "Gaz à effet de serre émis" from "editContextIndicators_indicator_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Ajout d'un indicateur contextualisé déjà existant
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un indicateur contextualisé"
    When I select "Général" from "editContextIndicators_context_addForm"
    And I select "Gaz à effet de serre émis" from "editContextIndicators_indicator_addForm"
    And I click "Valider"
    Then the field "editContextIndicators_context_addForm" should have error: "Il existe déjà un indicateur contextualisé pour ce contexte et cet indicateur."
    And the field "editContextIndicators_indicator_addForm" should have error: "Il existe déjà un indicateur contextualisé pour ce contexte et cet indicateur."
  # Vérification contenu datagrid
    When I click element "#editContextIndicators_addPanel a.btn:contains('Annuler')"
    Then the row 1 of the "editContextIndicators" datagrid should contain:
      | context       | indicator         | axes    |
      | Général | Gaz à effet de serre émis |   |
  # Suppression
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Ajout d'un indicateur contextualisé, avec axes deux à deux transverses
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un indicateur contextualisé"
    When I select "Général" from "editContextIndicators_context_addForm"
    And I select "Gaz à effet de serre émis" from "editContextIndicators_indicator_addForm"
    And I additionally select "Poste article 75" from "editContextIndicators_axes_addForm"
    And I additionally select "Gaz" from "editContextIndicators_axes_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 1 of the "editContextIndicators" datagrid should contain:
      | context       | indicator         | axes    |
      | Général | Gaz à effet de serre émis | Gaz, Poste article 75 |

  @javascript
  Scenario: classifContextIndicator2
    When I am on "classif/contextindicator/manage"
    Then I should see the "editContextIndicators" datagrid
  # Ajout d'un indicateur contextualisé
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un indicateur contextualisé"
    When I select "Général" from "editContextIndicators_context_addForm"
    And I select "Gaz à effet de serre émis" from "editContextIndicators_indicator_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Tentative de suppression de l'indicateur "Gaz à effet de serre émis"
    When I am on "classif/indicator/manage"
    And I wait 2 seconds
    Then the row 1 of the "editIndicators" datagrid should contain:
      | label       |
      | Gaz à effet de serre émis |
    When I click "Supprimer" in the row 1 of the "editIndicators" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cet indicateur ne peut pas être supprimé, car il est utilisé pour (au moins) un indicateur contextualisé."
    And the row 1 of the "editIndicators" datagrid should contain:
      | label       |
      | Gaz à effet de serre émis |
  # Tentative de suppression du contexte
    When I am on "classif/context/manage"
    And I wait 2 seconds
    Then the row 1 of the "editContexts" datagrid should contain:
      | label       |
      | Général |
    When I click "Supprimer" in the row 1 of the "editContexts" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce contexte ne peut pas être supprimé, car il est utilisé pour (au moins) un indicateur contextualisé."
    And the row 1 of the "editContexts" datagrid should contain:
      | label       |
      | Général |