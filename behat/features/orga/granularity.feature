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
  # Ajout d'une granularité
    When I click element "#orga_granularities a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    # When I fill in "s2id_autogen1" with "Année"
    When I select "Année" from "granularity_axes_addForm"
    # When I select "Pays" from "granularity_axes_addForm"
    # And I check "Navigable"
    And I click element "#granularity_addPanel button.btn:contains('Valider')"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
    And the row 2 of the "granularity" datagrid should contain:
      | axes  | navigable  | orgaTab |
      | Année | Non navigable  | Non     |
  # Ajout d'une granularité déjà existante
    When I click element "#orga_granularities a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I select "Année" from "granularity_axes_addForm"
    And I click element "#granularity_addPanel button.btn:contains('Valider')"
    # Then the field "s2id_granularity_axes_addForm" should have error: "Il existe déjà un niveau organisationnel correspondant à cette combinaison d'axes."
    When I click element "#granularity_addPanel a.btn:contains('Annuler')"
  # Suppression d'une granularité
    And I click "Supprimer" in the row 2 of the "granularity" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click element "#granularity_deletePanel a.btn:contains('Confirmer')"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "granularity" datagrid should contain 1 row