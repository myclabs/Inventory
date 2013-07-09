@dbFull
Feature: classifIndicator

  Background:
    Given I am logged in

  @javascript
  Scenario: classifIndicator1
    Given I am on "classif/indicator/manage"
    Then I should see the "editIndicators" datagrid
  # Ajout d'un indicateur, identifiant vide
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un indicateur"
    When I click "Valider"
    Then the field "editIndicators_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout d'un indicateur, identifiant avec des caractères non autorisés
    When I fill in "editIndicators_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "editIndicators_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # TODO : tester la validité des unités, une fois la fonctionnalité implémentée
  # Ajout d'un indicateur, saisie correcte
    When I fill in "editIndicators_label_addForm" with "À supprimer"
    And I fill in "editIndicators_ref_addForm" with "a_supprimer"
    And I fill in "editIndicators_unit_addForm" with "t_co2e"
    And I fill in "editIndicators_ratioUnit_addForm" with "kg_co2e"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 1 of the "editIndicators" datagrid should contain:
      | label       | ref         | unit    | ratioUnit |
      | À supprimer | a_supprimer | t_co2e  | kg_co2e   |
  # Ajout d'un indicateur, identifiant déjà utilisé
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un indicateur"
    And I fill in "editIndicators_ref_addForm" with "a_supprimer"
    And I click "Valider"
    Then the field "editIndicators_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Suppression d'un indicateur (non utilisé par un indicateur contextualisé)
    When I click element "#editIndicators_addPanel a.btn:contains('Annuler')"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."