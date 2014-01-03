@dbFull
Feature: Input in a cell associated to a DW feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Input in a cell associated to a DW scenario
  # Check that the results of input are indeed taken into account in the DW, refs #6468
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
  # Modification du formulaire associé à une zone-marque
    And I open tab "Formulaires"
    And I open collapse "Zone | Marque"
    And I set "Combustion de combustible, mesuré en unité de masse" for column "aF" of row 1 of the "aFGranularityConfig2" datagrid with a confirmation message
  # Accès à la cellule "Europe | Marque A"
    And I click element ".fa-plus"
    And I click element "#goTo2"
  # Accès à la saisie
    And I open collapse "Zone | Marque"
    And I click "Cliquer pour accéder" in the row 1 of the "aFGranularity2Input2" datagrid
  # Saisie
    And I select "Charbon" from "nature_combustible"
    And I fill in "quantite_combustible" with "10"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    When I click "Quitter"
  # Réalisation et lancement d'une analyse pour vérifier que la saisie a bien été prise en compte
    And I open tab "Analyses"
    And I click "Nouvelle analyse"
    And I click element "#indicatorRatio_indicator"
    And I select "Gaz" from "indicatorAxisOne"
    And I select "Histogramme vertical" from "chartType"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
    When I open tab "Valeurs"
    Then the row 1 of the "reportValues" datagrid should contain:
      | valueAxisc_gaz | valueDigital |
      | CO2            | 66,6        |






