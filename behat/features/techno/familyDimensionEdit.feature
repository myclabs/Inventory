@dbFull
Feature: Family dimension list edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Create family dimension, correct input
    Given I am on "techno/family/edit/id/3"
    And I wait for the page to finish loading
    Then I should see "Famille test vide"
    When I open tab "Général"
    Then I should see the "dimensions" datagrid
  # Affichage popup
    When I click element ".btn:contains('Ajouter')[data-target='#dimensions_addPanel']"
    Then I should see the popup "Ajout d'une dimension"
  # Ajout, saisie correcte
    When I select "Horizontale" from "dimensions_orientation_addForm"
    And I select "processus" from "dimensions_meaning_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the "dimensions" datagrid should contain 1 row
    And the row 1 of the "dimensions" datagrid should contain:
      | orientation | meaning   |
      | Horizontale | processus |

  @javascript
  Scenario: Create family dimension, incorrect input
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
    Then I should see "Famille test non vide"
    When I open tab "Général"
    And I click element ".btn:contains('Ajouter')[data-target='#dimensions_addPanel']"
    Then I should see the popup "Ajout d'une dimension"
  # Ajout, orientation vide, signification vide
    When I click "Valider"
    Then the field "Orientation" should have error: "Merci de renseigner ce champ."
    # And the field "Signification" should have error: "Merci de renseigner ce champ."
    And the field "dimensions_meaning_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, orientation saisie, signification déjà utilisée par une autre dimension
    When I select "Horizontale" from "Orientation"
    # And I select "combustible" from "Signification"
    And I select "combustible" from "dimensions_meaning_addForm"
    And I click "Valider"
    Then the field "dimensions_meaning_addForm" should have error: "Merci de choisir une autre signification, celle-ci est déjà associée à une autre dimension de cette famille."

  @javascript
  Scenario: Edit family dimension in family dimension datagrid
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
    Then I should see "Famille test non vide"
    When I open tab "Général"
    Then I should see the "dimensions" datagrid
  # Vérification contenu datagrid
  # D'abord les dimensions verticales, puis les horizontales
    And the row 1 of the "dimensions" datagrid should contain:
      | orientation | meaning     | members              |
      | Verticale   | combustible | charbon, gaz naturel |
    And the row 2 of the "dimensions" datagrid should contain:
      | orientation | meaning   | members                            |
      | Horizontale | processus | amont de la combustion, combustion |
  # Modification orientation
    When I set "Horizontale" for column "orientation" of row 1 of the "dimensions" datagrid with a confirmation message
  # La dimension se retrouve en dernière position des dimensions partageant sa nouvelle orientation
    And the row 2 of the "dimensions" datagrid should contain:
      | orientation | meaning     | members              |
      | Horizontale | combustible | charbon, gaz naturel |

  @javascript
  Scenario: Link toward the page of one of the family dimensions, and return
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
    Then I should see "Famille test non vide"
    When I open tab "Général"
    Then I should see the "dimensions" datagrid
  # Pour le contenu voir test précédent
  # Accès à la page d'édition de la dimension
    When I click "Cliquer pour accéder" in the row 1 of the "dimensions" datagrid
    Then I should see "Édition d'une dimension"
  # Retour à la page d'édition de la famille
    When I click "Retourner à la famille"
  # On ne retombe pas sur l'onglet "Général", mais sur l'onglet "Éléments"
    Then I should see "Processus"
    And I should see "0,123 ± 16 %"

  @javascript
  Scenario: Delete family dimension
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
    Then I should see "Famille test non vide"
    When I open tab "Général"
    Then I should see the "dimensions" datagrid
  # Pour le contenu voir test précédent
    When I click "Supprimer" in the row 1 of the "dimensions" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "dimensions" datagrid should contain 1 row
  # On rajoute la même dimension histoire de vérifier que les éléments ont bien été supprimés
    # TODO…
