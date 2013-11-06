@dbFull
Feature: Rebuild of dataware through the data rebuild tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Rebuild analysis data from the data rebuild tab, without launching form calculations scenario
  # Ajout d'un axe organisationnel
    Given I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    And I open tab "Paramétrage"
    And I open tab "Axes"
    And I wait 3 seconds
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Test"
    And I fill in "addAxis_ref" with "test"
    And I click "Valider"
    And I wait 3 seconds
    Then the following message is shown and closed: "Ajout effectué."
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/organization"
    And I wait for the page to finish loading
    And I click "Tester si la structure des données d'analyse est à jour"
    Then I should see "La structure des données d'analyse de l'organisation n'est pas à jour. Merci de cliquer une nouvelle fois sur ce bouton si vous souhaitez la mettre à jour."
  # Régénération
    When I click "La structure des données d'analyse de l'organisation n'est pas à jour. Merci de cliquer une nouvelle fois sur ce bouton si vous souhaitez la mettre à jour."
    And I wait 10 seconds
    Then the following message is shown and closed: "Régénération des données d'analyse effectuée."
    When I click "Tester si la structure des données d'analyse est à jour"
    Then I should see "La structure des données d'analyse de l'organisation est à jour."

  @javascript
  Scenario: Rebuild analysis data from the data rebuild tab, with launching form calculations scenario
  # Ajout d'un axe organisationnel
    Given I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    And I open tab "Paramétrage"
    And I open tab "Axes"
    And I wait 3 seconds
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Test"
    And I fill in "addAxis_ref" with "test"
    And I click "Valider"
    And I wait 3 seconds
    Then the following message is shown and closed: "Ajout effectué."
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/organization"
    And I wait for the page to finish loading
    And I click "Tester si la structure des données d'analyse est à jour"
    Then I should see "La structure des données d'analyse de l'organisation n'est pas à jour. Merci de cliquer une nouvelle fois sur ce bouton si vous souhaitez la mettre à jour."
  # Régénération
    When I click "La structure des données d'analyse de l'organisation n'est pas à jour. Merci de cliquer une nouvelle fois sur ce bouton si vous souhaitez la mettre à jour."
      # And I wait 10 seconds
    Then the following message is shown and closed: "Régénération des données d'analyse effectuée."
    When I click "Tester si la structure des données d'analyse est à jour"
    Then I should see "La structure des données d'analyse de l'organisation est à jour."

  @javascript
  Scenario: Rebuild analysis data from the data rebuild tab, without launching form calculations, and check analysis result scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Reconstruction"
    And I click "Régénérer les données d'analyse"
    And I wait 5 seconds
    Then the following message is shown and closed: "Régénération des données d'analyse effectuée."
  # Vérification que la régénération a bien fonctionné
    When I open tab "Analyses"
    Then I should see the "report" datagrid
    And the row 2 of the "report" datagrid should contain:
      | label                        |
      | Chiffre d'affaire, par année |
    When I click "Cliquer pour accéder" in the row 2 of the "report" datagrid
    And I open tab "Valeurs"
    Then the row 1 of the "reportValues" datagrid should contain:
      | valueAxiso_annee | valueDigital | valueUncertainty |
      | 2012                | 20           | 10%              |
    And the row 2 of the "reportValues" datagrid should contain:
      | valueAxiso_annee | valueDigital | valueUncertainty |
      | 2013                | 10           | 15%              |


  @javascript
  Scenario: Rebuild analysis data from the data rebuild tab, with launching form calculations, and check analysis result scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Reconstruction"
    And I click "Relancer les calculs et régénérer les données d'analyse"
  # Apparemment, besoin d'un peu d'attente pour que ça passe en local
    And I wait 5 seconds
    Then the following message is shown and closed: "Relance des calculs et régénération des données d'analyse effectuées."
  # Vérification que la régénération a bien fonctionné
    When I open tab "Analyses"
    Then I should see the "report" datagrid
    And the row 2 of the "report" datagrid should contain:
      | label                        |
      | Chiffre d'affaire, par année |
    When I click "Cliquer pour accéder" in the row 2 of the "report" datagrid
    And I open tab "Valeurs"
    Then the row 1 of the "reportValues" datagrid should contain:
      | valueAxiso_annee | valueDigital | valueUncertainty |
      | 2012                | 20           | 10%              |
    And the row 2 of the "reportValues" datagrid should contain:
      | valueAxiso_annee | valueDigital | valueUncertainty |
      | 2013                | 10           | 15%              |