@dbOneOrganization
Feature: orgaLibrary

  Background:
    Given I am logged in

  @javascript
  Scenario: orgaLibrary1
  # Modification à "Oui" de l'attribut "cellsWithDocs" de la granularité globale
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Niveaux"
    Then I should see the "granularity" datagrid
    When I set "Oui" for column "inputDocuments" of row 1 of the "granularity" datagrid with a confirmation message
    And I reload the page
    And I open tab "Documents"
    Then I should see "Documents des saisies"
    And I should see the "library1" datagrid
    When I click "Ajouter un nouveau document"
    Then I should see the popup "Ajout d'un document"
    When I click element "#library1_add .btn:contains('Ajouter')"
    Then I should see "L'adresse indiquée est vide ou invalide."
    And I click "Annuler"
