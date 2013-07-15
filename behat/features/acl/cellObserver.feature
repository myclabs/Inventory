@dbFull
Feature: Cell observer feature

  @javascript
  Scenario: Observer of a single cell
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "observateur.zone-marque@toto.com"
    And I fill in "password" with "observateur.zone-marque@toto.com"
    And I click "connection"
  # On tombe sur la page de la cellule
    Then I should see "Europe | Marque A Organisation avec données"
    When I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity2Input8" datagrid
    And the "aFGranularity2Input8" datagrid should contain 2 row
  # TODO : "Unité globale" non cliquable dans le volet de navigation
  # Accès à l'onglet "Collectes"
    When I open tab "Collectes"
    Then I should see the "inventories6" datagrid
    And the "inventories6" datagrid should contain 1 row
  # TODO : statut de la collecte non éditable
  # Accès à l'onglet "Analyses"
    When I open tab "Analyses"
    Then I should see the "report" datagrid
  # Clic sur "Export Excel détaillé"
    When I click "Export Excel détaillé"
    Then I should see "La génération du fichier est en cours. Une fenêtre de téléchargement devrait apparaître d'ici quelques secondes, merci de patienter."
  # Les autres onglets de la page d'une cellule sont absents
    # TODO And I should not see "Organisation"
    And I should not see "Rôles"
    And I should not see "Reconst. données"

  @javascript
  Scenario: Observer of several cells
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "observateur.site@toto.com"
    And I fill in "password" with "observateur.site@toto.com"
    And I click "connection"
  # On tombe sur le datagrid des cellules
    Then I should see the "listCells" datagrid
    And the "listCells" datagrid should contain 2 row
    And the row 1 of the "listCells" datagrid should contain:
      | label  | access      |
      | Annecy | Observateur |
  # Accès à une des cellules
    When I click "Cliquer pour accéder" in the row 1 of the "listCells" datagrid
    Then I should see "Annecy Organisation avec données"
    When I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity4Input8" datagrid
    And the "aFGranularity4Input8" datagrid should contain 1 row