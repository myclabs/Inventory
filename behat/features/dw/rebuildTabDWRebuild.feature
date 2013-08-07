@dbFull
Feature: Rebuild of dataware through the data rebuild tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Rebuild analysis data from the data rebuild tab, without launching form calculations
  # Ajout d'un axe organisationnel
    Given I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Axes"
    And I wait 5 seconds
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Test"
    And I fill in "addAxis_ref" with "test"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I open tab "Reconst. données"
    And I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
  # Vérification que la régénération a bien fonctionné
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."


  @javascript
  Scenario: Rebuild analysis data from the data rebuild tab, with launching form calculations
  # Ajout d'un axe organisationnel
    Given I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Axes"
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Test"
    And I fill in "addAxis_ref" with "test"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I open tab "Reconst. données"
    And I click "Relancer les calculs et régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
  # Vérification que la régénération a bien fonctionné
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."