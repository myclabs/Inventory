@dbFull
Feature: Organization administrator feature

  @javascript
  Scenario: Administrator of a single organization scenario
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "administrateur.workspace@toto.com"
    And I fill in "password" with "administrateur.workspace@toto.com"
    And I click "connection"
    And I wait 10 seconds
  # On tombe sur la liste des organisations
    Then I should see "Axes racine : Année, Site, Catégorie, Axe vide"
    And I should see "Collectes : Année | Zone | Marque"
  # Accès à l'organisation
    When I click "Workspace avec données"
    Then I should see "Workspace avec données"
    And I should see "Vue globale"
    When I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity1Input8" datagrid
  # Accès à l'onglet "Informations générales"
    When I open tab "Paramétrage"
    And I open tab "Informations générales"
  # Accès au datagrid des analyses préconfigurées
    And I open collapse "Niveau organisationnel global"
    Then I should see the "granularity1Report" datagrid
  # Accès à l'onglet "Rôles" et au datagrid des administrateurs d'organisation
    When I open tab "Rôles"
    And I open collapse "Administrateurs de workspace"
    Then I should see the "organizationACL1" datagrid
