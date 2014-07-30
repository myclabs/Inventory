@dbFull
Feature: Cell dataware analysis feature

  Background:
    Given I am logged in

  @javascript
  Scenario: New cell analysis scenario
  # Accès à l'onglet "Analyses" et au datagrid des analyses (cellule globale)
    Given I am on "orga/cell/view/cell/1"
    And I wait for the page to finish loading
    And I click element "#currentGranularity .cell .fa-bar-chart-o"
  # Nouvelle analyse
    And I click element "#reports1 .modal-footer .fa-plus"
    Then I should see "Nouvelle analyse"
    And I click "Lancer"
    Then the field "typeSumRatioChoice" should have error: "Merci de préciser la nature des valeurs à fournir."
    And the field "displayType" should have error: "Merci de préciser le type de graphique à afficher."
  # Retour
    When I click "Retour"
    Then I should see "Workspace avec données"
    And  I should see "Vue globale"

  @javascript @readOnly
  Scenario: Cell existing preconfigured analysis scenario
    Given I am on "orga/cell/view/cell/1"
    And I wait for the page to finish loading
    And I click element "#currentGranularity .cell .fa-bar-chart-o"
    Then I should see "Chiffre d'affaire 2012, marques A et B, par site"
    Then I should see "Chiffre d'affaire, par année"
    When I click "Chiffre d'affaire, par année"
    And I open tab "Valeurs"
    Then the row 1 of the "reportValues" datagrid should contain:
      | valueAxiso_annee | valueDigital | valueUncertainty |
      | 2012             | 20           | 10%              |
    And the row 2 of the "reportValues" datagrid should contain:
      | valueAxiso_annee | valueDigital | valueUncertainty |
      | 2013             | 10           | 15%              |

  @javascript
  Scenario: Filter on input status for an analysis scenario
  # Analyse sans filtre sur le statut
    Given I am on "orga/cell/view/cell/1"
    And I wait for the page to finish loading
    And I click element "a[href='#granularity4']"
    And I click element ".cell[data-tag='/1-zone:europe/1-pays:france/2-site:annecy/&/2-marque:marque_a/2-site:annecy/'] .fa-bar-chart-o"
    And I click element "#reports6 .modal-footer .fa-plus"
    Then I should see "Nouvelle analyse"
    And I select "sum" from "typeSumRatioChoice"
    And I select "1_ges" from "numeratorIndicator"
    And I select "Histogramme vertical" from "displayType"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
    When I open tab "Valeurs"
    Then the "reportValues" datagrid should contain 1 row
    And the row 1 of the "reportValues" datagrid should contain:
      | valueAxiso_annee | valueDigital | valueUncertainty |
      | 2012                | 33,3         | 23%              |
  # On filtre sur la valeur "Terminé" pour le statut
    When I open collapse "Filtres"
    And I click element "input[name='inputStatus_memberNumberChoice'][value='one']"
    And I select "Terminé" from "inputStatus_members[]"
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
    And I select "Complet" from "inputStatus_members[]"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
  # Focus pour scroller vers le haut
    When I focus on element "#saveReportButton.btn:contains('Enregistrer')"
  # Cette fois-ci aucun résultat ne sort
    And I open tab "Valeurs"
    Then the "reportValues" datagrid should contain 0 row
