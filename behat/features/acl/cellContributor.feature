@dbFull
Feature: Cell contributor feature

  @javascript @readOnly
  Scenario: Contributor of a single cell
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login
    When I fill in "email" with "contributeur.zone-marque@toto.com"
    And I fill in "password" with "contributeur.zone-marque@toto.com"
    And I click "connection"
  # On tombe sur la page de la cellule
    Then I should see "Europe | Marque A Workspace avec données"
  # Accès à une saisie et à l'historique des valeurs d'un champ (suite à détection bug droits utilisateur)
    When I wait 5 seconds
    And I open collapse "Zone | Marque"
    And I click "Cliquer pour accéder" in the row 1 of the "aFGranularity2Input2" datagrid
    And I click element "#chiffre_affaireHistory .btn"
    Then I should see "Historique des valeurs"
    And I should see a "code:contains('10 k€ ± 15 %')" element
  # Accès à l'onglet "Commentaires"
    When I open tab "Commentaires"
    And I click "Ajouter un commentaire"
    And I fill in "addContent" with "Blabla"
    And I click element "#Ajouter"
    Then I should see "Blabla"
  # Accès à l'onglet "Documents" (pb de configuration, pas de bibliothèque associée…)
    When I open tab "Documents"
  # Vérification que pas accès aux autres habituels onglets de la page d'une saisie
    And I should not see "Résultats"
    And I should not see "Détails calculs"
    When I open tab "Saisie"
    And I click "Quitter"
    # Vérification des onglets auxquels on a accès
    And I open tab "Historique"
    And I open tab "Commentaires"
    And I open tab "Saisies"
  # Vérification des onglets auxquels on n'a pas accès
    And I should not see "Paramétrage"
    And I should not see "Rôles"
    And I should not see "Collectes"
    And I should not see "Analyses"
    And I should not see "Exports"
    And I should not see "Reconstruction"

  @javascript @readOnly
  Scenario: Contributor of several cells
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login
    When I fill in "email" with "contributeur.site@toto.com"
    And I fill in "password" with "contributeur.site@toto.com"
    And I click "connection"
  # On tombe sur le datagrid des cellules
    Then I should see the "listCells" datagrid
    And the "listCells" datagrid should contain 2 row
    And the row 1 of the "listCells" datagrid should contain:
      | label  | access       |
      | Annecy | Contributeur |
  # Accès à une des cellules
    When I click "Accéder aux saisies" in the row 1 of the "listCells" datagrid
    Then I should see "Annecy Workspace avec données"
    When I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity5Input8" datagrid
