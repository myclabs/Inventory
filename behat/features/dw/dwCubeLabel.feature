@dbFull
Feature: Dw cube label feature

  Background:
    Given I am logged in

  @javascript
  Scenario: DW cube label scenario
  # On modifie le libellé de l'élément "Europe"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Paramétrage"
    And I open tab "Éléments"
    And I open collapse "Zone"
    Then the row 1 of the "listMemberszone" datagrid should contain:
      | label  |
      | Europe |
    When I set "Europe modifiée" for column "label" of row 1 of the "listMemberszone" datagrid with a confirmation message
  # Accès à la cellule "Europe modifiée | Marque A"
    And I click element ".icon-plus"
    And I click element "#goTo2"
    Then I should see "Europe modifiée | Marque A Workspace avec données"
  # Dans la page d'une nouvelle analyse le libellé "Europe modifié" apparaît déjà
    When I open tab "Analyses"
    And I click "Nouvelle analyse"
    Then I should see "Nouvelle analyse Europe modifiée | Marque A"
    When I click "Retour"
    And I click element ".icon-plus"
    And I click "Vue globale"
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
  #TODO Vérification que le libellé a bien été modifié (impossible à modifier, ne se voit que dans l'export)




