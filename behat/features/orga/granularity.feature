@dbOneOrganizationWithAxes
Feature: orgaGranularity

  Background:
    Given I am logged in

  @javascript
  Scenario: orgaGranularity1
  # Valeurs par défaut granularité globale, granularité ajoutée, ajout/suppression d'une granularité
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Organisation"
    And I open tab "Niveaux"
    Then I should see the "granularity" datagrid
  # Valeurs par défaut des attributs de la granularité globale
    And the row 1 of the "granularity" datagrid should contain:
      | axes  | navigable  | orgaTab | aCL | aFTab | dW  |
      |       | Navigable  | Oui     | Oui | Oui   | Oui |
  # Ajout d'une granularité avec un axe, non navigable
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Année" from "granularity_axes_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
    # Valeurs par défaut des attributs d'une granularité non globale
    And the row 2 of the "granularity" datagrid should contain:
      | axes  | navigable     | orgaTab | aCL | aFTab | dW  |
      | Année | Non navigable | Non     | Non | Non   | Non |
  # Ajout d'une granularité avec un axe, navigable
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Site" from "granularity_axes_addForm"
    And I check "Navigable"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
    And the row 2 of the "granularity" datagrid should contain:
      | axes | navigable | orgaTab |
      | Site | Navigable | Non     |
  # Ajout d'une granularité avec deux axes, navigable
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Zone" from "granularity_axes_addForm"
    And I additionally select "Marque" from "granularity_axes_addForm"
    And I check "Navigable"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
    And the row 2 of the "granularity" datagrid should contain:
      | axes         | navigable  | orgaTab |
      | Zone, Marque | Navigable  | Non     |
  # Suppression d'une granularité
    And I click "Supprimer" in the row 2 of the "granularity" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "granularity" datagrid should contain 3 row

  @javascript
  Scenario: orgaGranularity2
  # Ajout granularité existante, ajout granularité avec toutes les options cochées
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Organisation"
    And I open tab "Niveaux"
    Then I should see the "granularity" datagrid
  # Ajout d'une granularité
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Année" from "granularity_axes_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
  # Ajout d'une granularité déjà existante
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Année" from "granularity_axes_addForm"
    And I click "Valider"
    Then the field "granularity_axes_addForm" should have error: "Il existe déjà un niveau organisationnel correspondant à cette combinaison d'axes."
    When I click "Annuler"
  # Ajout d'une granularité avec toutes les options cochées
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Zone" from "granularity_axes_addForm"
    And I additionally select "Marque" from "granularity_axes_addForm"
    And I select "Navigable" in radio "Navigable"
    And I select "Oui" in radio "Organisation"
    And I select "Oui" in radio "Rôles"
    And I select "Oui" in radio "Formulaires"
    And I select "Oui" in radio "Analyses"
    And I select "Oui" in radio "Modèles d'action"
    And I select "Oui" in radio "Actions"
    And I select "Oui" in radio "Documents"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
    And the row 2 of the "granularity" datagrid should contain:
      | axes          | navigable  | orgaTab | aCL | aFTab | dW  | genericActions | contextActions | inputDocuments |
      | Zone, Marque  | Navigable  | Oui     | Oui | Oui   | Oui | Oui            | Oui            | Oui            |

  @javascript
  Scenario: orgaGranularity3
  # Édition d'une granularité
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Organisation"
    And I open tab "Niveaux"
    Then I should see the "granularity" datagrid
  # Édition des options de la granularité globale dans le datagrid des granularités
    When I set "Non" for column "aCL" of row 1 of the "granularity" datagrid with a confirmation message
    And I set "Non" for column "aFTab" of row 1 of the "granularity" datagrid with a confirmation message
    And I set "Non" for column "dW" of row 1 of the "granularity" datagrid with a confirmation message
    And I set "Oui" for column "inputDocuments" of row 1 of the "granularity" datagrid with a confirmation message
    Then the row 1 of the "granularity" datagrid should contain:
      | aCL | aFTab | dW  | inputDocuments |
      | Non | Non   | Non | Oui            |