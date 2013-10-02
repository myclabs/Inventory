@dbFull
Feature: Meanings datagrid feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a meaning
  # Affichage datagrid
    Given I am on "techno/meaning/list"
    And I wait for the page to finish loading
    Then I should see the "meaningDatagrid" datagrid
    And the row 1 of the "meaningDatagrid" datagrid should contain:
      | label       | ref         |
      | combustible | combustible |
  # Ajout d'un meaning
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une signification"
  # Clic sur "Valider", aucun meaning sélectionné
    When I click "Valider"
    Then the field "meaningDatagrid_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Clic sur "Valider", meaning existant sélectionné
    When I select "combustible" from "meaningDatagrid_ref_addForm"
    And I click "Valider"
    Then the field "meaningDatagrid_ref_addForm" should have error: "Cette signification existe déjà."
  # Bouton "Annuler"
    When I click "Annuler"
    And I click "Ajouter"
  # Clic sur "Valider", saisie correcte
    And I select "charbon" from "meaningDatagrid_ref_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."

  @javascript
  Scenario: Deletion of a meaning
    Given I am on "techno/meaning/list"
    And I wait for the page to finish loading
    Then I should see the "meaningDatagrid" datagrid
    And the row 1 of the "meaningDatagrid" datagrid should contain:
      | label       | ref         |
      | combustible | combustible |
  # Suppression, meaning utilisé dans une dimension
    When I click "Supprimer" in the row 2 of the "meaningDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cette signification ne peut pas être supprimmée, car elle est utilisée au moins une fois pour définir un tag ou une dimension d'une famille."
  # TODO : Suppression, meaning utilisé dans un tag
  # Suppression sans obstacle
  # On commence par l'ajout
  # TODO : disposer de l'ajout dans les scripts de paramétrage
    When I click "Ajouter"
    And I select "charbon" from "meaningDatagrid_ref_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 4 of the "meaningDatagrid" datagrid should contain:
      | label   | ref     |
      | charbon | charbon |
  # Suppression
    When I click "Supprimer" in the row 4 of the "meaningDatagrid" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."