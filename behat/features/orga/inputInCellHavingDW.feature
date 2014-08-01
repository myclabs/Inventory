@dbFull
Feature: Input in a cell associated to a DW feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Input in a cell associated to a DW scenario
  # Check that the results of input are indeed taken into account in the DW, refs #6468
    Given I am on "orga/workspace/edit/workspace/1"
    And I wait for the page to finish loading
  # Modification du formulaire associé à une zone-marque
    And I open tab "Formulaires"
    And I click "Zone | Marque Zone | Marque"
    And I set "Combustion de combustible, mesuré en unité de masse" for column "af" of row 1 of the "datagridCellAfs3" datagrid with a confirmation message
  # Accès à la cellule "Europe | Marque A"
  # Accès à la saisie
    And I am on "orga/cell/input/cell/3/fromCell/1/"
    And I wait for the page to finish loading
  # Saisie
    And I select "Charbon" from "nature_combustible"
    And I fill in "quantite_combustible" with "10"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    When I click "Quitter"
  # Réalisation et lancement d'une analyse pour vérifier que la saisie a bien été prise en compte
    And I click element "a[data-target='#reports3']"
    And I click element "#reports3 .modal-footer i.fa-plus"
    And I click element "input[value='sum']"
    And I select "GES" from "numeratorIndicator"
    And I select "Gaz" from "sumAxisOne"
    And I select "vertical_chart" from "displayType"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
    When I open tab "Valeurs"
    Then the row 1 of the "reportValues" datagrid should contain:
      | valueAxisc_1_gaz | valueDigital |
      | CO2            | 66,6         |






