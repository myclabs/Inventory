@dbFull
Feature: Dw cube label feature

  Background:
    Given I am logged in

  @javascript
  Scenario: DW cube label scenario
  # On modifie le libellé du membre "Europe"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Membres"
    And I open collapse "Zone"
    Then the row 1 of the "listMemberszone" datagrid should contain:
      | label  |
      | Europe |
    When I set "Europe modifiée" for column "label" of row 1 of the "listMemberszone" datagrid with a confirmation message
  # Accès à la cellule "Europe modifiée | Marque A"
    And I click element "#goTo2"
    Then I should see "Europe modifiée | Marque A Organisation avec données"
  # Dans la page d'une nouvelle analyse le libellé "Europe modifié" apparaît déjà
    When I open tab "Analyses"
    And I click "Nouvelle analyse"
    Then I should see "Nouvelle analyse Europe modifiée | Marque A"
    When I click "Retour"
    And I click "Vue globale"
    And I open tab "Analyses"
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    And I wait 5 seconds
    Then the following message is shown and closed: "Régénération des données d'analyse effectuée."
    When I reload the page
    And I wait for the page to finish loading
    And I open tab "Analyses"
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Vérification que le libellé a bien été modifié (???)




