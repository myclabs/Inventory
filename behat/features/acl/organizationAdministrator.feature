@dbFull
Feature: Organization administrator feature

  @javascript @readOnly
  Scenario: Administrator of a single workspace scenario
    Given I am on the homepage
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "administrateur.workspace@toto.com"
    And I fill in "password" with "administrateur.workspace@toto.com"
    And I click "connection"
  # On tombe sur la liste des organisations
    And I should see "Workspace avec données"
  # Accès à l'organisation
    When I click element "tr.workspace h4 a:contains('Workspace avec données')"
    Then I should see "Workspace avec données"
    And I should see "Vue globale"
    And I should see "2012 Annecy Énergie"
  # Accès à l'onglet "Informations générales"
    When I click element "h1 small a"
  # Accès au datagrid des analyses préconfigurées
    Then I should see "Config. Analyses"
    And I should see "Rôles"