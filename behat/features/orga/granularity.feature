@dbOneOrganizationWithAxes
Feature: orgaGranularity

  Background:
    Given I am logged in

  @javascript
  Scenario: orgaGranularity1
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Organisation"
    And I open tab "Niveaux"
    Then I should see the "granularity" datagrid
    And the row 1 of the "granularity" datagrid should contain:
      | axes  | navigable  | orgaTab |
      |       | Navigable  | Non     |
  # Ajout d'une granularité avec un axe, non navigable
    When I click element "#orga_granularities a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Année" from "granularity_axes_addForm"
    And I click element "#granularity_addPanel button.btn:contains('Valider')"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
    And the row 2 of the "granularity" datagrid should contain:
      | axes  | navigable  | orgaTab |
      | Année | Non navigable  | Non     |
  # Ajout d'une granularité avec un axe, navigable
    When I click element "#orga_granularities a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Site" from "granularity_axes_addForm"
    And I check "Navigable"
    And I click element "#granularity_addPanel button.btn:contains('Valider')"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
    And the row 2 of the "granularity" datagrid should contain:
      | axes  | navigable  | orgaTab |
      | Site | Navigable  | Non     |
  # Ajout d'une granularité avec deux axes, navigable
    When I click element "#orga_granularities a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Zone" from "granularity_axes_addForm"
    And I additionally select "Marque" from "granularity_axes_addForm"
    And I check "Navigable"
    And I click element "#granularity_addPanel button.btn:contains('Valider')"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
    And the row 2 of the "granularity" datagrid should contain:
      | axes  | navigable  | orgaTab |
      | Zone, Marque | Navigable  | Non |
  # Suppression d'une granularité
    And I click "Supprimer" in the row 2 of the "granularity" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click element "#granularity_deletePanel a.btn:contains('Confirmer')"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "granularity" datagrid should contain 3 row

  @javascript
  Scenario: orgaGranularity2
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Organisation"
    And I open tab "Niveaux"
    Then I should see the "granularity" datagrid
  # Ajout d'une granularité
    When I click element "#orga_granularities a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Année" from "granularity_axes_addForm"
    And I click element "#granularity_addPanel button.btn:contains('Valider')"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
  # Ajout d'une granularité déjà existante
    When I click element "#orga_granularities a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Année" from "granularity_axes_addForm"
    And I click element "#granularity_addPanel button.btn:contains('Valider')"
    Then the field "granularity_axes_addForm" should have error: "Il existe déjà un niveau organisationnel correspondant à cette combinaison d'axes."
    When I click element "#granularity_addPanel a.btn:contains('Annuler')"
  # Ajout d'une granularité avec toutes les options cochées
    When I click element "#orga_granularities a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I additionally select "Zone" from "granularity_axes_addForm"
    And I additionally select "Marque" from "granularity_axes_addForm"
    # And I select "Navigable" in radio "granularity_axes_addForm"
    # And I select "Oui" in radio "granularity_orgaTab_addForm"
    # And I check "Navigable"
    # And I select "Navigable" in radio "granularity_navigable_addForm"
    And I select "Navigable" in radio "Navigable"
    And I select "Oui" in radio "Organisation"
    And I select "Oui" in radio "Rôles"
    And I select "Oui" in radio "Formulaires"
    And I select "Oui" in radio "Analyses"
    And I select "Oui" in radio "Modèles d'action"
    And I select "Oui" in radio "Actions"
    And I select "Oui" in radio "Documents"
    And I click element "#granularity_addPanel button.btn:contains('Valider')"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
    And the row 2 of the "granularity" datagrid should contain:
      | axes          | navigable  | orgaTab | aCL | aFTab | dW  | genericActions | contextActions | inputDocuments |
      | Zone, Marque  | Navigable  | Oui     | Oui | Oui   | Oui | Oui            | Oui            | Oui            |
