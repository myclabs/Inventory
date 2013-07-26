@dbFull
Feature: Family dimension edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Create family dimension, correct input
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
    Then I should see "Famille test de processus"
    When I open tab "Général"
    And I click element ".btn:contains('Ajouter')[data-target='#tags_addPanel']"
    Then I should see the popup "Ajout d'une dimension"

  @javascript
  Scenario: Create family dimension, incorrect input
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
    Then I should see "Famille test de processus"
    When I open tab "Général"
    And I click element ".btn:contains('Ajouter')[data-target='#tags_addPanel']"
    Then I should see the popup "Ajout d'une dimension"
  # Ajout, orientation vide, signification vide
    When I click "Valider"
    Then the field "Orientation" should have  error: "Merci de renseigner ce champ."
    And the field "Signification" should have  error: "Merci de renseigner ce champ."
  # Ajout, orientation saisie, signification déjà utilisée par une autre dimension
    When I select "Horizontale" from "Orientation"
    And I select "combustible" from "Signification"
    And I click "Valider"
    Then the field "Signification" should have  error: "Merci de choisir une autre signification, celle-ci est déjà associée à une autre dimension de la même famille"

  @javascript
  Scenario: Edit family dimension
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
    Then I should see "Famille test de processus"
    When I open tab "Général"
    Then I should see the "dimensions" datagrid
  # Vérification contenu datagrid
  # D'abord les dimensions verticales, puis les horizontales
    And the row 1 of the "dimensions" datagrid should contain:
      | orientation | meaning     | members              |
      | Verticale   | combustible | charbon, gaz naturel |
    And the row 2 of the "dimensions" datagrid should contain:
      | orientation | meaning   | members                            |
      | Horizontale | processus | amont de la combustion, combustion |
  # Modification orientation
    When I set "Horizontale" for column "orientation" of row 1 of the "dimensions" datagrid with a confirmation message
  # La dimension se retrouve en dernière position des dimensions partageant sa nouvelle orientation
    And the row 2 of the "dimensions" datagrid should contain:
      | orientation | meaning     | members              |
      | Horizontale | combustible | charbon, gaz naturel |
