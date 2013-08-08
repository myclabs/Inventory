@dbFull
Feature: Cell dataware analysis feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Access to analysis configuration page
  # Accès à l'onglet "Analyses" et au datagrid des analyses (cellule globale)
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Analyses"
    Then I should see the "report" datagrid
  # Accès à l'export Excel détaillé (on teste juste que le bouton est cliquable)
    # When I click "Export Excel détaillé"
  # Nouvelle analyse
    When I click "Nouvelle analyse"
    And I click "Lancer"
    Then the field "indicatorRatio" should have error: "Merci de préciser la nature des valeurs à fournir."
    And the field "chartType" should have error: "Merci de préciser le type de graphique à afficher."
  # Retour
    When I click "Retour"
    Then I should see "Vue globale Organisation avec données"

