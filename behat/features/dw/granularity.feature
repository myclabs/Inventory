@dbFull
Feature: granularityDw

  Background:
    Given I am logged in

  @javascript
  Scenario: Trying to launch an analysis when configuration is not complete
  # Affichage des messages d'erreur lorsque des champs ne sont pas remplis
  # Accès à l'onglet "Configuration"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Organisation"
    And I open tab "Configuration"
  # Accès au datagrid des analyses pré-configurées au niveau global
    And I open collapse "Niveau organisationnel global"
    Then I should see the "granularity1Report" datagrid
  # Nouvelle analyse
    When I click "Nouvelle analyse"
  # Tentative de lancement, sans avoir rien saisi
    And I click "Lancer"
    Then the field "indicatorRatio" should have error: "Merci de préciser la nature des valeurs à fournir."
    And the field "chartType" should have error: "Merci de préciser le type de graphique à afficher."
  # Tentative de lancement sans avoir précisé l'indicateur
    When I click element "#indicatorRatio_indicator"
    And I select "Camembert" from "chartType"
    And I click "Lancer"
    Then the field "indicator" should have error: "Merci de sélectionner un indicateur."
  # Sélection "Ratio"
    When I click element "#indicatorRatio_ratio"
    And I click "Lancer"
    Then the field "numerator" should have error: "Merci de sélectionner un indicateur pour le numérateur."
    And the field "denominator" should have error: "Merci de sélectionner un indicateur pour le dénominateur."
  # Retour
    When I click "Retour"
    Then I should see "Unité organisationnelle globale Organisation test"

  @javascript
  Scenario: Display of the status of analysis configuration
  # Affichage du cartouche indiquant le statut de la configuration
    Given I am on "orga/granularity/report/idCell/1/idGranularity/1/idCube/1"
  # Nouvelle analyse
    Then I should see "Nouvelle configuration"
    And I should not see "Modifications en cours"
  # Clic sur "Indicateur"
    When I click element "#indicatorRatio_indicator"
    Then I should not see "Nouvelle configuration"
    And I should see "Modifications en cours"
  # Réinitialisation
    When I click element "#resetReportConfiguration"
    Then I should see "Nouvelle configuration"
    And I should not see "Modifications en cours"
  # Clic sur "Ratio"
    When I click element "#indicatorRatio_indicator"
    Then I should not see "Nouvelle configuration"
    And I should see "Modifications en cours"
  # Réinitialisation
    When I click element "#resetReportConfiguration"
    Then I should see "Nouvelle configuration"
    And I should not see "Modifications en cours"
  # Sélection du type de graphique
    When I select "Camembert" from "chartType"
    Then I should not see "Nouvelle configuration"
    And I should see "Modifications en cours"
  # Réinitialisation
    When I click element "#resetReportConfiguration"
    Then I should see "Nouvelle configuration"
    And I should not see "Modifications en cours"
  # Clic sur la case à cocher "Afficher l'incertitude"
    When I click element "#withUncertainty"
    Then I should not see "Nouvelle configuration"
    And I should see "Modifications en cours"
  # Réinitialisation
    When I click element "#resetReportConfiguration"
    Then I should see "Nouvelle configuration"
    And I should not see "Modifications en cours"
  # Édition d'un filtre
    When I open collapse "Filtres"
    # Besoin que le dw comprenne les axes organisationnels
    # And I click element "#filterAxisorga_anneeNumberMembers_one"
    # Then I should not see "Nouvelle configuration"
    # And I should see "Modifications en cours"





