@dbFull
Feature: Organization library feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Display of documents tab, acces to the documents datagrid
  # Accès au datagrid des granularités
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Paramétrage"
    And I open tab "Niveaux"
    Then I should see the "granularity" datagrid
    And the row 1 of the "granularity" datagrid should contain:
      | axes  | inputDocuments |
      |       | Non            |
    And the row 3 of the "granularity" datagrid should contain:
      | axes  | inputDocuments |
      | Site  | Oui            |
  # Vérification que l'onglet "Documents" est absent au niveau global
    When I open tab "Saisies"
    Then I should not see "Documents"
  # Vérification que l'onglet "Documents" est présent au niveau site, accès à l'onglet "Documents"
    When I click element ".fa-plus"
    And I select "Annecy" from "site"
    And I click element "#goTo3"
    And I open tab "Documents"
    Then I should see "Documents des saisies"
    And I should see the "library1" datagrid

  @javascript
  Scenario: Upload a document
  # Accès au datagrid des documents
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    When I click element ".fa-plus"
    And I select "Annecy" from "site"
    And I click element "#goTo3"
    And I open tab "Documents"
    Then I should see the "library1" datagrid
  # Ajout d'un nouveau document
    When I click "Ajouter un nouveau document"
    Then I should see the popup "Ajout d'un document"
    When I click element "#library1_add .btn:contains('Ajouter')"
    Then I should see "L'adresse indiquée est vide ou invalide."
    And I click "Annuler"
