@dbFull
Feature: orgaConfiguration

  Background:
    Given I am logged in

  @javascript
  Scenario: orgaConfiguration1
  # Accès au datagrid des granularités de saisie
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Configuration"
    Then I should see the "inputGranularities" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel de saisie, et du niveau organisationnel correspondant pour le choix des formulaires comptables"
  # Ajout, granularité de choix des formulaires vide
    When I select "Niveau organisationnel global" from "Saisie"
    And I click "Valider"
    Then the field "Choix des formulaires" should have error: "Merci de renseigner ce champ."
  # Ajout, granularité de saisie vide
    When I select "" from "Saisie"
    When I select "Niveau organisationnel global" from "Choix des formulaires"
    And I click "Valider"
    Then the field "Saisie" should have error: "Merci de renseigner ce champ."
  # Ajout, saisie correcte, granularités identiques
    When I select "Niveau organisationnel global" from "Saisie"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 1 of the "inputGranularities" datagrid should contain:
      | inputGranularity              | inputConfigGranularity        |
      | Niveau organisationnel global | Niveau organisationnel global |
  # Suppression (aucun formulaire configuré)
    When I click "Supprimer" in the row 1 of the "inputGranularities" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "inputGranularities" datagrid should contain 0 row
