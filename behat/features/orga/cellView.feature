@dbFull
Feature: Cell view feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Manage cell users
    # Ajout d'un contributeur et d'un observateur
    Given I am on "orga/cell/view/cell/1"
    And I wait for the page to finish loading
    Then I should see "Europe Marque B"
    And I should see a "tr.cell[data-tag='/1-zone:europe/&/2-marque:marque_b/'] a.show-users:contains('(0)')" element
    When I click element "tr.cell[data-tag='/1-zone:europe/&/2-marque:marque_b/'] a.show-users i"
    Then I should see the popup "Rôles utilisateur —  Europe | Marque B"
    When I fill in "userEmail4" with "contributor@test.behat"
    And I select "Contributeur" from "userRole4"
    And I click "Ajouter"
    And I wait 2 seconds
    Then I should see "Ajout effectué."
    When I click "Ajouter"
    Then I should see "Ce rôle est déjà attribué à l'utilisateur indiqué."
    When I fill in "userEmail4" with "observator@test.behat"
    And I select "Observateur" from "userRole4"
    And I click "Ajouter"
    And I wait 2 seconds
    Then I should see "Ajout effectué."

    # Rechargement et vérification
    When I am on "orga/cell/view/cell/1"
    And I wait for the page to finish loading
    Then I should see "Europe Marque B"
    And I should see a "tr.cell[data-tag='/1-zone:europe/&/2-marque:marque_b/'] a.show-users:contains('(2)')" element
    When I select "Tous statuts de collecte" from "granularity8_inventoryStatus"
    And I click element "tr.cell[data-tag='/1-annee:1-2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/'] a.show-inventory-users i"
    Then I should see the popup "Participants — 2012 | Grenoble"
    And I should see "contributor@test.behat"
    And I should not see "observator@test.behat"
    Then I click element "div#inventoryUsers32 div.modal-header button"

    # Suppression du contributeur
    When I click element "tr.cell[data-tag='/1-zone:europe/&/2-marque:marque_b/'] a.show-users i"
    Then I should see the popup "Rôles utilisateur —  Europe | Marque B"
    And I should see "contributor@test.behat"
    When I click element "a.delete-user[href='orga/cell/remove-user/cell/4/role/19/'] i"
    Then I should see "Êtes-vous sûr de vouloir priver cet utilisateur de ce rôle ? contributor@test.behat"
    When I click "Confirmer"
    And I wait 2 seconds
    Then I should see "Suppression effectuée."

    # Rechargement et vérification
    When I am on "orga/cell/view/cell/1"
    And I wait for the page to finish loading
    Then I should see "Europe Marque B"
    And I should see a "tr.cell[data-tag='/1-zone:europe/&/2-marque:marque_b/'] a.show-users:contains('(1)')" element
    When I select "Tous statuts de collecte" from "granularity8_inventoryStatus"
    And I click element "tr.cell[data-tag='/1-annee:1-2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/'] a.show-inventory-users i"
    Then I should see the popup "Participants — 2012 | Grenoble"
    And I should not see "contributor@test.behat"
    And I should not see "observator@test.behat"
