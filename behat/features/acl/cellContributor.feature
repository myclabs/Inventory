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
    Then I should see "Workspace avec données"
    When I click element "tr.workspace h4 a:contains('Workspace avec données')"
    And I wait for the page to finish loading
    Then I should see "Workspace avec données"
    And I should see "Europe | Marque A"
  # Accès à une saisie et à l'historique des valeurs d'un champ (suite à détection bug droits utilisateur)
    And I click element "div[id='currentGranularity'] a.go-input"
    And I click element "#chiffre_affaireHistory"
    Then I should see "Historique des valeurs"
    And I wait 2 seconds
    And I should see a "code:contains('10 k€ ± 15 %')" element
    And I click element "#chiffre_affaireHistory"
  # Accès à l'onglet "Commentaires"
    When I open tab "Commentaires"
    And I click "Ajouter un commentaire"
    And I fill in "newComment" with "Blabla"
    And I click "Ajouter un commentaire"
    And I wait 2 seconds
    Then I should see "Blabla"
  # Accès à l'onglet "Documents" (pb de configuration, pas de bibliothèque associée…)
    When I open tab "Documents"
  # Vérification que pas accès aux autres habituels onglets de la page d'une saisie
    And I should not see "Résultats"
    And I should not see "Détails calculs"
    When I open tab "Saisie"
    And I click "Quitter"
    # Vérification des onglets auxquels on a accès
    And I should not see "Paramétrage"

  @javascript @readOnly
  Scenario: Contributor of several cells
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login
    When I fill in "email" with "contributeur.site@toto.com"
    And I fill in "password" with "contributeur.site@toto.com"
    And I click "connection"
  # On tombe sur le datagrid des cellules
    Then I should see "Workspace avec données"
    When I click element "tr.workspace h4 a:contains('Workspace avec données')"
    And I wait for the page to finish loading
    Then I should see "Workspace avec données"
    And I should see "Contributeur Annecy"
    And I should see "Contributeur Chambéry"
  # Accès à une des cellules
    When I click "Contributeur Annecy"
    Then I should see "Annecy"

  @javascript @readOnly
  Scenario: Contributor can edit an input
    Given I am logged in as "contributeur.zone-marque@toto.com"
    Given I am on "orga/cell/input/cell/30/fromCell/3/"
    And I wait 3 seconds
  # On va sur la page de la cellule
    Then I should see "Saisie 2012 | Annecy"
    When I fill in "chiffre_affaire" with "100"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    When I click "Terminer la saisie"
    Then the following message is shown and closed: "Saisie terminée."
