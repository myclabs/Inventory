@dbOneOrganizationWithAxes
Feature: granularityDw

  Background:
    Given I am logged in

  @javascript
  Scenario: granularityDw1
  # Accès à l'onglet "Configuration"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Organisation"
    And I open tab "Configuration"
  # Accès au datagrid des analyses pré-configurées au niveau global
    And I open collapse "Niveau organisationnel global"
    Then I should see the "granularity1Report" datagrid
  # Nouvelle analyse
    When I click "Nouvelle analyse"
    Then I should see "Nouvelle configuration"
  # Tentative de lancement, sans avoir rien saisi
    When I click "Lancer"
    Then the field "indicatorRatio" should have error: "Merci de préciser la nature des valeurs à fournir."
    And the field "chartType" should have error: "Merci de préciser le type de graphique à afficher."
  # Modification et test d'affichage du cartouche indiquant la modification en cours
    When I check "Indicateur"
    # Then I should see "Modifications en cours"
  # Retour
    When I click "Retour"
    Then I should see "Unité organisationnelle globale Organisation test"





