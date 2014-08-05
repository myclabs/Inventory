@dbFull
Feature: Organization List feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a workspace, link details, default attributes
  # Affichage datagrid des organisations
    Given I am on "account/dashboard"
    And I wait for the page to finish loading
  # Ajout d'une organisation
    And I click element "a[data-original-title='Créer un nouveau workspace']"
    When I fill in "workspaceLabel" with "Test Behat"
    And I click element "form[id='addWorkspace'] div.navigation-buttons:nth-child(1) button.navigation-next"
    And I click element "form[id='addWorkspace'] div.navigation-buttons:nth-child(1) button.navigation-end"
    Then I should see the popup "Initialisation du workspace"
    And I wait for 5 seconds
    And I should see "Ajout effectué"
    When I click "Revenir à l'accueil"
  # Workspaces affichées dans l'ordre de création
  # TODO : ordre alphabétique des libellés ?
    Then I should see the "Test Behat" workspace
  # Lien vers le détail de l'organisation
    And I click element ".workspace a:contains('Test Behat')"
    Then I should see "Test Behat"
    And I should see "Vue globale"
  # Vérification de la création de la granularité globale et ses attributs par défaut
    When I click "Paramétrage"
    And I wait for the page to finish loading
    And I open tab "Niveaux"
    Then I should see the "granularity3" datagrid
    And the "granularity3" datagrid should contain 1 row
    And the row 1 of the "granularity3" datagrid should contain:
      | axes | relevance | input | afs | inventory | reports | acl |
      |      | Non       | Non   | Non | -         | Non     | Non |

  @javascript
  Scenario: Deletion of a workspace without data scenario
  # Affichage datagrid des organisations
    When I am on "account/dashboard"
    And I wait for the page to finish loading
  # Ajout d'une organisation, pour la supprimer ensuite
    And I click element "a[data-original-title='Créer un nouveau workspace']"
    Then I should be on "orga/workspace/add/account/1"
    When I fill in "workspaceLabel" with "Test Behat"
    And I click element "form[id='addWorkspace'] div.navigation-buttons:nth-child(1) button.navigation-next"
    And I click element "form[id='addWorkspace'] div.navigation-buttons:nth-child(1) button.navigation-end"
    Then I should see the popup "Initialisation du workspace"
    And I wait for 5 seconds
    And I should see "Ajout effectué"
    When I click "Revenir à l'accueil"
  # Suppression de l'organisation ajoutée
    When I click element ".workspace:nth-child(1) a:contains('Supprimer')"
    Then I should see a popup
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée"
    And I should not see "Test behat"
  # TODO : tester la suppression d'une organisation non vide

