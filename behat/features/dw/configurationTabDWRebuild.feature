@dbFull
Feature: Rebuild of dataware from the configuration tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Rebuild analysis data from the configuration tab scenario
  # Accès à l'onglet "Informations générales"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Paramétrage"
    And I open tab "Informations générales"
  # Au départ la structure des données d'analyse est à jour
    And I click "Tester si la structure des données d'analyse est à jour"
    Then I should see "La structure des données d'analyse de l'organisation est à jour"
  # Ajout d'un axe organisationnel
    When I open tab "Axes"
    And I wait 5 seconds
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Test"
    And I fill in "addAxis_ref" with "test"
    And I click "Valider"
    And I wait 5 seconds
    Then the following message is shown and closed: "Ajout effectué."
  # Détection modification
    And I open tab "Informations générales"
    And I click "Tester si la structure des données d'analyse est à jour"
    Then I should see "La structure des données d'analyse de l'organisation n'est pas à jour. Merci de cliquer une nouvelle fois sur ce bouton si vous souhaitez la mettre à jour."
  # Régénération
    When I click "La structure des données d'analyse de l'organisation n'est pas à jour. Merci de cliquer une nouvelle fois sur ce bouton si vous souhaitez la mettre à jour."
    And I wait 10 seconds
    Then the following message is shown and closed: "Régénération des données d'analyse effectuée."
    When I click "Tester si la structure des données d'analyse est à jour"
    Then I should see "La structure des données d'analyse de l'organisation est à jour"
