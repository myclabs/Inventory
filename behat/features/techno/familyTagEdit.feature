@dbFull
Feature: Family tag edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Create family tag, correct input
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
    Then I should see "Famille test de processus"
    When I open tab "Général"
    And I click element ".btn:contains('Ajouter')[data-target='#tags_addPanel']"
    Then I should see the popup "Ajout d'un tag"



  @javascript
  Scenario: Create family tag, incorrect input



  @javascript
  Scenario: Delete family tag