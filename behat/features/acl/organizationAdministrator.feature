@dbFull
Feature: Organization administrator feature

  @javascript
  Scenario: Administrator of a single organization
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "administrateur.organisation@toto.com"
    And I fill in "password" with "administrateur.organisation@toto.com"
    And I click "connection"
  # On tombe sur le datagrid des organisations
    Then I should see the "organizations" datagrid
    And the "organizations" datagrid should contain 1 row
  # Accès à l'organisation
    When I click "Détails" in the row 1 of the "organizations" datagrid
    Then I should see "Vue globale Organisation avec données"
    When I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity1Input8" datagrid
    And the "aFGranularity1Input8" datagrid should contain 8 row
  # Accès à l'onglet "Configuration"
    When I open tab "Organisation"
    And I open tab "Configuration"
  # Accès au datagrid des analyses préconfigurées
    And I open collapse "Niveau organisationnel global"
    Then I should see the "granularity1Report" datagrid
  # Accès à l'onglet "Rôles" et au datagrid des administrateurs d'organisation
    When I open tab "Rôles"
    And I open collapse "Administrateurs d'organisation"
    Then I should see the "organizationACL1" datagrid


