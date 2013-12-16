@dbFull
Feature: Organizational member deletion feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Deletion of an organizational member generating cells with inputs and DW, without or with roles
    # Accès à l'onglet "Éléments"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Paramétrage"
    And I open tab "Éléments"
    And I open collapse "Site"
    Then I should see the "listMemberssite" datagrid
    And the "listMemberssite" datagrid should contain 3 row
    And the row 3 of the "listMemberssite" datagrid should contain:
      | label  |
      | Grenoble |
    # Remarque : Grenoble associé à aucun rôle
    When I click "Supprimer" in the row 3 of the "listMemberssite" datagrid
    And I click "Confirmer"
    And I wait 5 seconds
    Then the following message is shown and closed: "Suppression effectuée."
    And the "listMemberssite" datagrid should contain 2 row
    # Tentative de suppression d'un élément générant une cellule associée à des rôles
    And the row 1 of the "listMemberssite" datagrid should contain:
      | label  |
      | Annecy |
    When I click "Supprimer" in the row 1 of the "listMemberssite" datagrid
    And I click "Confirmer"
    Then the following message is shown and closed: "Cet élément ne peut pas être supprimé, car il existe au moins un rôle organisationnel pour une unité organisationnelle associée à cet élément."
    And the row 1 of the "listMemberssite" datagrid should contain:
      | label  |
      | Annecy |

  @javascript
  Scenario: Deletion of an organizational member scenario
  #6268 Exceptions non capturées suppression d'un élément organisationnel
  # Accès à l'onglet "Éléments"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Paramétrage"
    And I open tab "Éléments"
  # Élément jouant le rôle de parent direct pour au moins un autre élément
    And I open collapse "Pays"
    When I click "Supprimer" in the row 1 of the "listMemberspays" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cet élément ne peut pas être supprimé, car il joue le rôle de parent direct pour au moins un autre élément."
    # Suppression d'un élément, sans obstacle
    When I open collapse "Année"
    And I click "Supprimer" in the row 1 of the "listMembersannee" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    Then the "listMembersannee" datagrid should contain 1 row