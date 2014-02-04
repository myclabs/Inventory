@dbFull
Feature: Cell comment tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Cell comment tab scenario
    Given I am on "orga/cell/view/idCell/1"
    And I wait for the page to finish loading
  # Au départ, aucun commentaire
    Then I should see "Aucun commentaire."
  # Ajout d'un commentaire dans une saisie
    When I click element ".current-cell .input-actions a"
    And I open tab "Commentaires"
    And I click "Ajouter un commentaire"
    And I fill in "addContent" with "h1. Commentaire cellule _globale_."
    And I click element "#Ajouter"
    Then I should see "Commentaire cellule globale."
  # Retour à la page de la cellule globale
    When I open tab "Saisie"
    And I click "Quitter"
  # Ajout second commentaire
    And I click element ".cell[data-tag='/1-zone:europe/&/2-marque:marque_a/'] .input-actions a"
    And I open tab "Commentaires"
    And I click "Ajouter un commentaire"
    And I fill in "addContent" with "h2. Commentaire cellule _Europe Marque A_."
    And I click element "#Ajouter"
    Then I should see "Commentaire cellule Europe Marque A."
  # Retour à la page de la cellule globale
    When I open tab "Saisie"
    And I click "Quitter"
  # Accès à l'onglet "Commentaires"
    Then I should see "par Administrateur Système à propos de la saisie Vue globale : « Commentaire cellule globale. »."
    And I should see "par Administrateur Système à propos de la saisie Europe | Marque A : « Commentaire cellule Europe Marque A. »."

