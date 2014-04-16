@dbFull
Feature: Family one dimension edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Add member to a family dimension, correct input
    Given I am on "parameter/family/edit/id/5"
    And I wait for the page to finish loading
  # Affichage famille et en-tête
    Then I should see "Famille test non vide"
    And I should see "Combustible : combustible"
    And I should see the "combustibleMembersDatagrid" datagrid
    And the "combustibleMembersDatagrid" datagrid should contain 2 row
    When I click element "#combustibleAddMemberButton"
    Then I should see the popup "Ajout d'une liste d'éléments"
    And I fill in "inputMemberList" with "Amont combustion; amont_combustion"
    Then I should see "1 élément(s) prêts à être ajoutés"
    When I click element "#addMemberPopup button:contains('Ajouter')"
    Then the following message is shown and closed: "1 élément(s) ont été ajouté(s)."
    And the "combustibleMembersDatagrid" datagrid should contain 3 row
  # Le nouvel élément a été ajouté à la fin
    And the row 3 of the "combustibleMembersDatagrid" datagrid should contain:
      | label            | ref              |
      | Amont combustion | amont_combustion |

  @javascript
  Scenario: Add member to a family dimension with label only
    Given I am on "parameter/family/edit/id/5"
    And I wait for the page to finish loading
  # Affichage famille et en-tête
    Then I should see "Famille test non vide"
    And I should see "Combustible : combustible"
    And I should see the "combustibleMembersDatagrid" datagrid
    And the "combustibleMembersDatagrid" datagrid should contain 2 row
    When I click element "#combustibleAddMemberButton"
    Then I should see the popup "Ajout d'une liste d'éléments"
    And I fill in "inputMemberList" with "Amont combustion"
    Then I should see "1 élément(s) prêts à être ajoutés"
    When I click element "#addMemberPopup button:contains('Ajouter')"
    Then the following message is shown and closed: "1 élément(s) ont été ajouté(s)."
    And the "combustibleMembersDatagrid" datagrid should contain 3 row
  # Le nouvel élément a été ajouté à la fin
    And the row 3 of the "combustibleMembersDatagrid" datagrid should contain:
      | label            | ref              |
      | Amont combustion | amont_combustion |

  @javascript
  Scenario: Add member to a family dimension, incorrect input
    Given I am on "parameter/dimension/details/id/5"
    And I wait for the page to finish loading
    And I click "combustibleAddMemberButton"
    Then I should see the popup "Ajout d'une liste d'éléments"
    And I fill in "inputMemberList" with " "
    Then I should see "Saisie non reconnue, merci de respecter le format précisé."

  @javascript
  Scenario: Delete member
    Given I am on "parameter/dimension/details/id/5"
    And I wait for the page to finish loading
    When I click "Supprimer" in the row 1 of the "combustibleMembersDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "combustibleMembersDatagrid" datagrid should contain 1 row
