@dbFull
Feature: Family dimension list edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Create family dimension, correct input
    Given I am on "parameter/family/edit/id/4"
    And I wait for the page to finish loading
    Then I should see "Famille test vide"
  # Affichage popup
    When I click "Ajout d'une dimension"
    Then I should see the popup "Ajout d'une dimension"
  # Ajout, saisie correcte
    When I fill in "addDimensionRef" with "ma_dimension"
    And I fill in "addDimensionLabel" with "Ma dimension"
    And I select "Horizontale" in radio "Orientation"
    And I click "Ajouter"
    Then the following message is shown and closed: "Ajout effectué."
    And I should see "ma_dimension"
    And I should see "Ma dimension"
    And I should see the "ma_dimensionMembersDatagrid" datagrid

  @javascript
  Scenario: Edit family dimension
    Given I am on "parameter/family/edit/id/5"
    And I wait for the page to finish loading
    Then I should see "Famille test non vide"

    And I should see "Combustible : combustible"
    And I should see the "combustibleMembersDatagrid" datagrid
    And the "combustibleMembersDatagrid" datagrid should contain 2 row

    And I should see "Processus : processus"
    And I should see the "processusMembersDatagrid" datagrid
    And the "processusMembersDatagrid" datagrid should contain 2 row

  @javascript
  Scenario: Delete family dimension
    Given I am on "parameter/family/edit/id/5"
    And I wait for the page to finish loading
    Then I should see "Famille test non vide"
  # Pour le contenu voir test précédent
    When I click element "div[data-dimension='combustible'] .deleteDimensionButton"
    And I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And I should not see "combustible"
  # On rajoute la même dimension histoire de vérifier que les éléments ont bien été supprimés
    # TODO…
