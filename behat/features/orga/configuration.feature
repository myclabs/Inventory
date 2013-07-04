@dbOneOrganizationWithAxes
Feature: orgaConfiguration

  Background:
    Given I am logged in

  @javascript
  Scenario: orgaConfiguration1
  # Accès au datagrid des granularités de saisie
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Organisation"
    And I open tab "Configuration"
    Then I should see the "inputGranularities" datagrid
  # Popup d'ajout
    When I click element "#orga_organization .btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un niveau organisationnel de saisie, et du niveau organisationnel correspondant pour le choix des formulaires comptables"
  # Ajout, granularité de choix des formulaires vide
    When I select "Niveau organisationnel global" from "inputGranularities_inputGranularity_addForm"
    And I click element "#inputGranularities_addPanel .btn:contains('Valider')"
    Then the field "inputGranularities_inputConfigGranularity_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, granularité de saisie vide
    When I select "" from "inputGranularities_inputGranularity_addForm"
    When I select "Niveau organisationnel global" from "inputGranularities_inputConfigGranularity_addForm"
    And I click element "#inputGranularities_addPanel .btn:contains('Valider')"
    Then the field "inputGranularities_inputGranularity_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, saisie correcte, granularités identiques
    When I select "Niveau organisationnel global" from "inputGranularities_inputGranularity_addForm"
    And I click element "#inputGranularities_addPanel .btn:contains('Valider')"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 1 of the "inputGranularities" datagrid should contain:
      | inputGranularity            | inputConfigGranularity |
      | Niveau organisationnel global | Niveau organisationnel global |
  # Suppression (aucun formulaire configuré)
    When I click "Supprimer" in the row 1 of the "inputGranularities" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click element "#inputGranularities_deletePanel .btn:contains('Confirmer')"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "inputGranularities" datagrid should contain 0 row






