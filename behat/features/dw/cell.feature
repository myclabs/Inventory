@dbFull
Feature: Cell dataware analysis feature

  Background:
    Given I am logged in

  @javascript
  Scenario: New cell analysis scenario
  # Accès à l'onglet "Analyses" et au datagrid des analyses (cellule globale)
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Analyses"
    Then I should see the "report" datagrid
  # Nouvelle analyse
    When I click "Nouvelle analyse"
    And I click "Lancer"
    Then the field "indicatorRatio" should have error: "Merci de préciser la nature des valeurs à fournir."
    And the field "chartType" should have error: "Merci de préciser le type de graphique à afficher."
  # Retour
    When I click "Retour"
    Then I should see "Vue globale Workpsace avec données"

  @javascript
  Scenario: Cell existing preconfigured analysis scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Analyses"
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
  Scenario: Filter on input status for an analysis scenario
  # Analyse sans filtre sur le statut
    Given I am on "orga/cell/details/idCell/5/tab/analyses"
    And I wait for the page to finish loading
    When I click "Nouvelle analyse"
    And I click element "#indicatorRatio_indicator"
    And I select "Histogramme vertical" from "chartType"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
    When I open tab "Valeurs"
    Then the "reportValues" datagrid should contain 1 row
    And the row 1 of the "reportValues" datagrid should contain:
      | valueAxiso_annee | valueDigital | valueUncertainty |
      | 2012                | 33,3         | 23%              |
  # On filtre sur la valeur "Terminé" pour le statut
    When I open collapse "Filtres"
    And I click element "#filterAxisinputStatusNumberMembers .radio:contains('Un')"
    And I select "Terminé" from "selectAxisinputStatusMemberFilter"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
  # Focus pour scroller vers le haut pour pouvoir ouvrir l'onglet "Valeurs"
    When I focus on element "#saveReportButton.btn:contains('Enregistrer')"
  # Résultat inchangé
    And I open tab "Valeurs"
    Then the "reportValues" datagrid should contain 1 row
    And the row 1 of the "reportValues" datagrid should contain:
      | valueAxiso_annee | valueDigital | valueUncertainty |
      | 2012                | 33,3         | 23%              |
  # On filtre sur la valeur "Complet" pour le statut
    And I select "Complet" from "selectAxisinputStatusMemberFilter"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
  # Focus pour scroller vers le haut
    When I focus on element "#saveReportButton.btn:contains('Enregistrer')"
  # Cette fois-ci aucun résultat ne sort
    And I open tab "Valeurs"
    Then the "reportValues" datagrid should contain 0 row
