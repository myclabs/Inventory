@dbFull
Feature: Organization datagrid feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an organization, link details, default attributes
  # Affichage datagrid des organisations
    Given I am on "orga/organization/manage"
    And I wait for the page to finish loading
    Then I should see the "organizations" datagrid
  # Ajout d'une organisation
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une organisation"
    When I fill in "Libellé" with "AAA"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
  # Organisations affichées dans l'ordre de création
  # TODO : ordre alphabétique des libellés ?
    And the row 2 of the "organizations" datagrid should contain:
      | label  |
      | AAA   |
  # Lien vers le détail de l'organisation
    When I click "Cliquer pour accéder" in the row 2 of the "organizations" datagrid
    Then I should see "Vue globale AAA"
  # Vérification de la création de la granularité globale et ses attributs par défaut
    When I open tab "Organisation"
    And I open tab "Niveaux"
    Then I should see the "granularity" datagrid
    And the "granularity" datagrid should contain 1 row
    And the row 1 of the "granularity" datagrid should contain:
      | axes | navigable | orgaTab | aCL | aFTab | dW  | genericActions | contextActions | inputDocuments |
      |      | Navigable | Oui     | Oui | Oui   | Oui | Non            | Non            | Non            |
  # Structure des données d'analyse par défaut (à jour)
    When I open tab "Configuration"
    And I click "Tester si la structure des données d'analyse est à jour"
    Then I should see "La structure des données d'analyse de l'organisation est à jour"

  @javascript
  Scenario: Deletion of an organization
  # Affichage datagrid des organisations
    When I am on "orga/organization/manage"
    And I wait for the page to finish loading
    Then I should see the "organizations" datagrid
    When I click "Supprimer" in the row 1 of the "organizations" datagrid
  # TODO: suppression d'une organisation
