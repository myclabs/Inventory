@dbFull
Feature: Keywords relations

  Background:
    Given I am logged in

  @javascript
  Scenario: Keywords relations edition
    Given I am on "http://localhost/inventory/keyword/association/manage"
    Then I should see the "association" datagrid
  # Ajout d'une relation, champs non renseignés
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une relation entre mots clés"
    When I click "Valider"
    Then the field "association_subject_addForm" should have error: "Merci de renseigner ce champ."
    And the field "association_predicate_addForm" should have error: "Merci de renseigner ce champ."
    And the field "association_predicate_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout d'une relation, relation déjà existante
    When I fill in "association_subject_addForm" with "combustible"
    And I select "est plus général que" from "association_predicate_addForm"
    And I fill in "association_subject_addForm" with "gaz naturel"
    And I click "Valider"
    Then the field "association_predicate_addForm" should have error: "Les deux mots clés indiqués sont déjà reliés par le même prédicat."
  # Ajout d'une relation, saisie valide
    When I fill in "association_subject_addForm" with "processus"
    And I select "est plus général que" from "association_predicate_addForm"
    And I fill in "association_subject_addForm" with "amont_combustion"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Modification du prédicat d'une relation
    When I set "contient" for column "predicate" of row 1 of the "association" datagrid with a confirmation message
    And the row 1 of the "association" datagrid should contain:
      | predicate |
      | contient  |
  # Suppression d'une relation
    When I click "Supprimer" in the row 1 of the "association" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."

