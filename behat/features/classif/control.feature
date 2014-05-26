@dbFull
Feature: Control of classification data

  Background:
    Given I am logged in

  @javascript @readOnly
  Scenario: Control of classification data scenario
    When I am on "classification/library/view/id/1"
    Then I should see "Contrôle de validité du paramétrage"
    When I click "Rechercher des anomalies"
    And I wait 2 seconds
    Then I should see "Axe ne contenant aucun élément : Axe vide"
    And I should see "Élément sans enfant d'un axe non situé à la racine : Scope : { Poste article 75 : [2, 3] }"
    And I should see "Élément pour lequel manque un élément parent : Poste article 75 : { Scope : [Élément sans parent] }"