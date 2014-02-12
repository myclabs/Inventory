@dbFull
Feature: Organization datagrid feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an organization, link details, default attributes
  # Affichage datagrid des organisations
    Given I am on "orga/organization/manage"
    And I wait for the page to finish loading
  # Ajout d'une organisation
    And I click "Ajouter"
    Then I should be on "orga/organization/add"
    When I fill in "organizationLabel" with "Test Behat"
    And I click element "form[id='addOrganization'] > div > div:nth-child(1) button.navigation-next"
    And I click element "form[id='addOrganization'] > div > div:nth-child(1) button.navigation-end"
    Then I should see the popup "Construction de l'organisation"
    And I wait for 5 seconds
    And I should see "Ajout effectué"
    When I click "Revenir à l'accueil"
  # Workspaces affichées dans l'ordre de création
  # TODO : ordre alphabétique des libellés ?
    And I should see "Test Behat"
  # Lien vers le détail de l'organisation
    And I click "Test Behat"
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
      |      | Non       | Non   | Non | Non       | Non     | Oui |

  @javascript
  Scenario: Deletion of an organization without data scenario
  # Affichage datagrid des organisations
    When I am on "orga/organization/manage"
    And I wait for the page to finish loading
  # Ajout d'une organisation, pour la supprimer ensuite
    And I click "Ajouter"
    Then I should be on "orga/organization/add"
    When I fill in "organizationLabel" with "Test Behat"
    And I click element "form[id='addOrganization'] > div > div:nth-child(1) button.navigation-next"
    And I click element "form[id='addOrganization'] > div > div:nth-child(1) button.navigation-end"
    Then I should see the popup "Construction de l'organisation"
    And I wait for 5 seconds
    And I should see "Ajout effectué"
    When I click "Revenir à l'accueil"
  # Suppression de l'organisation ajoutée
    When I click element ".organization:nth-child(3) a:contains('Supprimer')"
    Then I should see a popup
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée"
    And I should not see "Test behat"
  # TODO : tester la suppression d'une organisation non vide

