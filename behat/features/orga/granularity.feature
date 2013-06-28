@dbOneOrganizationWithAxes
Feature: OrgaGranularity

  Background:
    Given I am logged in

  @javascript
  Scenario: OrgaGranularityEdit1
  # Accès à l'onglet "Niveaux"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Organisation"
    And I open tab "Niveaux"
    Then I should see the "granularity" datagrid
  # Ajout d'une granularité
    When I click element "#orga_granularities a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un niveau organisationnel"
    When I fill in "s2id_autogen1" with "Année"
    And I check "Navigable"
    And I wait 10 seconds
    And I click element "#granularity_addPanel button.btn:contains('Valider')"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
  # Suppression d'une granularité
    When I click "Supprimer" in the row 2 of the "granularity" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."