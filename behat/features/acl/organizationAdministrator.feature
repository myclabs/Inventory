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

  @javascript @readOnly
  Scenario: Administrator can edit an input
    Given I am logged in as "administrateur.global@toto.com"
    Given I am on "orga/cell/input/cell/3/fromCell/3/"
    And I wait 3 seconds
# On va sur la page de la cellule
    Then I should see "Saisie Europe | Marque A"
    When I fill in "chiffre_affaire" with "100"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    When I click "Terminer la saisie"
    Then the following message is shown and closed: "Saisie terminée."