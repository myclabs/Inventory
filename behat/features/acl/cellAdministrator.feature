@dbFull
Feature: Cell administrator feature

  @javascript
  Scenario: Administrator of a single cell
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "administrateur.zone-marque@toto.com"
    And I fill in "password" with "administrateur.zone-marque@toto.com"
    And I click "connection"
  # On tombe sur la page de la cellule
    Then I should see "Europe | Marque A Organisation avec données"
    When I wait 5 seconds
    And I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity2Input8" datagrid
  # Vérification que le libellé "Vue globale" est présent mais non cliquable
  # Voir "Organization navigation scenario"
    And I should see "Vue globale"
    And I should not see a "#navigationParent a:contains('Vue globale')" element
  # Vérification qu'on a bien accès à l'onglet "Organisation" et à ses sous-onglets
    When I open tab "Organisation"
  # On tombe sur l'onglet "Membres"
    And I open collapse "Pays"
    And I open collapse "Site"
    Then I should see the "listMemberspays" datagrid
    And I should see the "listMemberssite" datagrid
  # Onglet "Sous-unités"
    When I open tab "Sous-unités"
    And I open collapse "Site"
    Then I should see the "child_c2_g3" datagrid
  # Onglet "Pertinence"
    When I open tab "Pertinence"
    And I open collapse "Site"
    Then I should see the "relevant_c2_g3" datagrid


  @javascript
  Scenario: Administrator of several cells
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "administrateur.site@toto.com"
    And I fill in "password" with "administrateur.site@toto.com"
    And I click "connection"
  # On tombe sur le datagrid des cellules
    Then I should see the "listCells" datagrid
    And the "listCells" datagrid should contain 2 row
    And the row 1 of the "listCells" datagrid should contain:
      | label  | access         |
      | Annecy | Administrateur |
  # Accès à une des cellules
    When I click "Cliquer pour accéder" in the row 1 of the "listCells" datagrid
    Then I should see "Annecy Organisation avec données"
    When I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity5Input8" datagrid
    And the "aFGranularity5Input8" datagrid should contain 2 row