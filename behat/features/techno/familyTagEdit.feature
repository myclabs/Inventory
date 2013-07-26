@dbFull
Feature: Family tag edit feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Create family tag, correct input
    Given I am on "techno/family/edit/id/3"
    And I wait for the page to finish loading
    Then I should see "Famille test vide"
    When I open tab "Général"
    Then I should see the "tags" datagrid
  # Affichage popup
    When I click element ".btn:contains('Ajouter')[data-target='#tags_addPanel']"
    Then I should see the popup "Ajout d'un tag"
  # Ajout, saisie correcte
    When I select "combustible" from "tags_meaning_addForm"
    # When I select "combustible" from "Signification"
    And I select "charbon" from "tags_value_addForm"
    # And I select "charbon" from "Valeur"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the "tags" datagrid should contain 1 row
    And  the row 1 of the "tags" datagrid should contain:
      | meaning     | value   |
      | combustible | charbon |

  @javascript
  Scenario: Create family tag, incorrect input
    Given I am on "techno/family/edit/id/4"
    And I wait for the page to finish loading
    Then I should see "Famille test non vide"
    When I open tab "Général"
    And I click element ".btn:contains('Ajouter')[data-target='#tags_addPanel']"
    Then I should see the popup "Ajout d'un tag"
  # Ajout, signification vide, valeur vide
    When I click "Valider"
    Then the field "Signification" should have error: "Merci de renseigner ce champ."
    And the field "Valeur" should have error: "Merci de renseigner ce champ."
  # Ajout, tag déjà existant
    # TODO…

  @javascript
  Scenario: Delete family tag
    # TODO…