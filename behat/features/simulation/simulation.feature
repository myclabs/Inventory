@dbFull
Feature: General data simulation feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a simulation scenario, correct input
  # Accès à l'onglet "Simulations"
    Given I am on "simulation/set/manage"
    And I wait for the page to finish loading
    Then I should see the "listSet" datagrid
  # Ajout d'un jeu de simulations
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un jeu de simulations"
    When I select "Catégorie contenant un formulaire - Données générales" from "Formulaire comptable"
    And I fill in "listSet_labelSet_addForm" with "aaa"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 1 of the "listSet" datagrid should contain:
      | labelAF                                               | labelSet | nbPrimarySet |
      | Catégorie contenant un formulaire - Données générales | aaa      | 0            |
  # Accès au datagrid des scénarios du jeu de simulations
    When I click "Cliquer pour accéder" in the row 1 of the "listSet" datagrid
    Then I should see "aaa Données générales"
    And I should see the "listScenarios" datagrid
  # Ajout d'un scénario
    When I click "Ajouter"
    And I fill in "Libellé" with "bbb"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 1 of the "listScenarios" datagrid should contain:
      | labelScenario | advancementInput | stateInput |
      | bbb           |                  |            |

  @javascript
  Scenario: Creation of a simulation scenario and of a simulation scenario, incorrect input
    Given I am on "simulation/set/manage"
    And I wait for the page to finish loading
  # Ajout, formulaire comptable non renseigné
    When I click "Ajouter"
    And I click "Valider"
    Then the field "Formulaire comptable" should have error: "Merci de renseigner ce champ."
  # L'ajout avec libellé vide est autorisé
    When I select "Catégorie contenant un formulaire - Données générales" from "Formulaire comptable"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Accès au datagrid des scénarios du jeu de simulations
    When I click "Cliquer pour accéder" in the row 1 of the "listSet" datagrid
  # Ajout d'un scénario, libellé vide (libellé obligatoire, manifestement)
    When I click "Ajouter"
    And I click "Valider"
    Then the field "Libellé" should have error: "Merci de renseigner ce champ."

  @javascript
  Scenario: Deletion of a simulation scenario, no input
    Given I am on "simulation/set/manage"
    And I wait for the page to finish loading
    And I click "Ajouter"
    And I select "Catégorie contenant un formulaire - Données générales" from "Formulaire comptable"
    And I fill in "listSet_labelSet_addForm" with "aaa"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    When I click "Supprimer" in the row 1 of the "listSet" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "listSet" datagrid should contain 0 row


