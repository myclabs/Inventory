@dbFull
Feature: Family dimension list edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Create family dimension, correct input
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
    Then I should see "Famille test vide"
  # Affichage popup
    When I click "Ajout d'une dimension"
    Then I should see the popup "Ajout d'une dimension"
  # Ajout, saisie correcte
    When I fill in "Identifiant" with "ma_dimension"
    And I fill in "Libellé" with "Ma dimension"
    And I select "Horizontale" in radio "Orientation"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And I should see "ma_dimension"
    And I should see "Ma dimension"
    And I should see the "ma_dimensionMembers" datagrid

  @javascript
  Scenario: Edit family dimension
    Given I am on "techno/family/edit/id/5"
    And I wait for the page to finish loading
    Then I should see "Famille test non vide"
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
  Scenario: Delete family dimension
    Given I am on "techno/family/edit/id/5"
    And I wait for the page to finish loading
    Then I should see "Famille test non vide"
  # Pour le contenu voir test précédent
    When I click element "div[data-dimension='combustible'] .deleteDimensionButton"
    And I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And I should not see "combustible"
  # On rajoute la même dimension histoire de vérifier que les éléments ont bien été supprimés
    # TODO…
