@dbEmpty
Feature: OrganizationsNOK

  Background:
    Given I am logged in

  @javascript
  Scenario: Orga edition
    Then I should see the "organizations" datagrid
    # Then I should see "Aucune donnée à afficher"
    When I follow "Ajouter"
    Then I should see the popup "Ajout d'une organisation"
    When I fill in "Libellé" with "Organisation-test"
    When I press "Valider"
    And I wait for the page to finish loading
    Then I should see "Ok : Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
    When I press "x"
    Then I should not see "Ok : Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps."
    And I reload the page
    Then the row 1 of the "organizations" datagrid should contain:
      | label      | details   | delete |
      | Organisation-test   | Détails   | Supprimer  |
    And I follow "Details"
    And I wait for the page to finish loading
    Then I should see "Unité organisationnelle globale Organisation-test"