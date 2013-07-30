@dbFull
Feature: Organization input tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Filter on organization members in Input tab
  # Accès à l'onglet "Saisies"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Saisies"
    And I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity1Input8" datagrid
    And the "aFGranularity1Input8" datagrid should contain 8 row
  # Filtre sur le site "Annecy"
    When I open collapse "Filtres"
    And I select "Annecy" from "aFGranularity1Input8_site_filterForm"
    And I click "Filtrer"
    Then the "aFGranularity1Input8" datagrid should contain 2 row
  # Bouton "Réinitialiser"
    When I click "Réinitialiser"
    Then the "aFGranularity1Input8" datagrid should contain 8 row

  @javascript
  Scenario: Display of input tab when the inventory granularity has not been defined
    Given I am on "orga/organization/manage"
    And I wait for the page to finish loading
    Then I should see the "organizations" datagrid
  # Ajout d'une organisation
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une organisation"
    When I fill in "Libellé" with "Test"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
    And the row 2 of the "organizations" datagrid should contain:
      | label  |
      | Test   |
  # Lien vers le détail de l'organisation
    When I click "Cliquer pour accéder" in the row 2 of the "organizations" datagrid
    Then I should see "Vue globale Test"
    # TODO : ajouter message pour indiquer qu'aucune granularité n'a été associée à des saisies.

