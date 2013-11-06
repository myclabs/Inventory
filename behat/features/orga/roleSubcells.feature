@dbFull
Feature: Organization role for subcells feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Display of collapses of roles by user for subcells scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Rôles"
    And I open collapse "Zone | Marque — par utilisateur"
    Then I should see the "granularityUserACL2" datagrid
    And the row 1 of the "granularityUserACL2" datagrid should contain:
      | zone   | marque   | userEmail                           | userRole       |
      | Europe | Marque A | administrateur.zone-marque@toto.com | Administrateur |
    When I close collapse "Zone | Marque — par utilisateur"
    And I open collapse "Site — par utilisateur"
    Then I should see the "granularityUserACL3" datagrid
    And the row 1 of the "granularityUserACL3" datagrid should contain:
      | Site   | userEmail                           | userRole       |
      | Annecy | administrateur.site@toto.com        | Administrateur |
    When I click "goTo2"
    And I open tab "Rôles"
    And I open collapse "Site — par utilisateur"
    Then I should see the "granularityUserACL3" datagrid
    And the row 1 of the "granularityUserACL3" datagrid should contain:
      | Site   | userEmail                           | userRole       |
      | Annecy | administrateur.site@toto.com        | Administrateur |
    When I click "goTo3"
    And I open tab "Rôles"
    Then I should not see "Site — par utilisateur"

  @javascript
  Scenario: Display of collapses of roles by user for subcells with filters scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Rôles"
    And I open collapse "Zone | Marque — par utilisateur"
    Then I should see the "granularityUserACL2" datagrid
    And the "granularityUserACL2" datagrid should contain 3 row
    When I open collapse "Filtres"
    And I select "Marque B" from "granularityUserACL2_marque_filterForm"
    And I click "Filtrer"
    Then the "granularityUserACL2" datagrid should contain 0 row
    When I close collapse "Zone | Marque — par utilisateur"
    And I open collapse "Site — par utilisateur"
    Then I should see the "granularityUserACL3" datagrid
    And the "granularityUserACL3" datagrid should contain 6 row
    When I open collapse "Filtres"
    And I select "Chambéry" from "granularityUserACL3_site_filterForm"
    And I click "Filtrer"
    Then the "granularityUserACL3" datagrid should contain 3 row

  @javascript
  Scenario: Create role for subcell, incorrect input scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Rôles"
    And I open collapse "Zone | Marque — par utilisateur"
    And I click "Ajouter"
    Then I should see the popup "Création d'un utilisateur ou attribution d'un rôle à un utilisateur existant"
  # Tentative d'ajout, adresse e-mail et rôle vides
    When I click "Valider"
    Then the field "granularityUserACL2_userEmail_addForm" should have error: "Merci de renseigner ce champ."
    And the field "granularityUserACL2_userRole_addForm" should have error: "Merci de renseigner ce champ."
  # Tentative d'ajout, rôle déjà existant
    When I fill in "granularityUserACL2_userEmail_addForm" with "administrateur.zone-marque@toto.com"
    And I select "Administrateur" from "granularityUserACL2_userRole_addForm"
    And I click "Valider"
    Then the field "granularityUserACL2_userRole_addForm" should have error: "Ce rôle est déjà attribué à l'utilisateur indiqué."

  @javascript
  Scenario: Create role for subcell, correct input
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Rôles"
    And I open collapse "Zone | Marque — par utilisateur"
    Then I should see the "granularityUserACL2" datagrid
    And the "granularityUserACL2" datagrid should contain 3 row
  # Ajout, utilisateur non existant
    When I click "Ajouter"
    Then I should see the popup "Création d'un utilisateur ou attribution d'un rôle à un utilisateur existant"
    When I select "Europe" from "s2id_granularityUserACL2_zone_addForm"
    And I select "Marque B" from "s2id_granularityUserACL2_marque_addForm"
    And I fill in "granularityUserACL2_userEmail_addForm" with "emmanuel.risler.abo@gmail.com"
    And I select "Contributeur" from "granularityUserACL2_userRole_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the "granularityUserACL2" datagrid should contain 4 row
    And the row 4 of the "granularityUserACL2" datagrid should contain:
      | zone   | marque   | userEmail                     | userRole     |
      | Europe | Marque B | emmanuel.risler.abo@gmail.com | Contributeur |
  # Ajout, utilisateur existant
    When I click "Ajouter"
    Then I should see the popup "Création d'un utilisateur ou attribution d'un rôle à un utilisateur existant"
    When I select "Europe" from "s2id_granularityUserACL2_zone_addForm"
    And I select "Marque B" from "s2id_granularityUserACL2_marque_addForm"
    And I fill in "granularityUserACL2_userEmail_addForm" with "emmanuel.risler.pro@gmail.com"
    And I select "Contributeur" from "granularityUserACL2_userRole_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the "granularityUserACL2" datagrid should contain 5 row
    And the row 5 of the "granularityUserACL2" datagrid should contain:
      | zone   | marque   | userEmail                     | userRole     |
      | Europe | Marque B | emmanuel.risler.pro@gmail.com | Contributeur |

  @javascript
  Scenario: Delete role for subcell scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Rôles"
    And I open collapse "Zone | Marque — par utilisateur"
    Then I should see the "granularityUserACL2" datagrid
    And the "granularityUserACL2" datagrid should contain 3 row
    When I click "Supprimer" in the row 1 of the "granularityUserACL2" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée"
    And the "granularityUserACL2" datagrid should contain 1 row

  @javascript
  Scenario: Display of collapses of roles by cell for subcells scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Rôles"
  # Contenu collapse au niveau Zone | Marque
    And I open collapse "Zone | Marque — par élément d'organisation"
    Then I should see the "granularityCellACL2" datagrid
    And the row 1 of the "granularityCellACL2" datagrid should contain:
      | zone   | marque   | administrators                                                           | details     |
      | Europe | Marque A | administrateur.zone-marque@toto.com \| contributeur.zone-marque@toto.com | 1 \| 1 \| 1 |
  # Contenu popup
    When I click "1 | 1 | 1" in the row 1 of the "granularityCellACL2" datagrid
    Then I should see the "cellACLs2" datagrid
    And the row 1 of the "cellACLs2" datagrid should contain:
      | userEmail                           | userRole       |
      | administrateur.zone-marque@toto.com | Administrateur |
    When I click "×"
    And I close collapse "Zone | Marque — par élément d'organisation"
  # Contenu collapse au niveau Site
    And I open collapse "Site — par élément d'organisation"
    Then I should see the "granularityCellACL3" datagrid
    And the row 1 of the "granularityCellACL3" datagrid should contain:
      | site   | administrators                                            | details     |
      | Annecy | administrateur.site@toto.com | contributeur.site@toto.com | 1 \| 1 \| 1 |
  # Contenu popup
    When I click "1 | 1 | 1" in the row 1 of the "granularityCellACL3" datagrid
    Then I should see the "cellACLs5" datagrid
    And the row 1 of the "cellACLs5" datagrid should contain:
      | userEmail                    | userRole       |
      | administrateur.site@toto.com | Administrateur |
    When I click "×"
  # On descend au niveau zone | marque
    When I click element ".icon-plus"
    And I click "goTo2"
    And I open tab "Rôles"
  # Contenu collapse au niveau Site
    And I open collapse "Site — par élément d'organisation"
    Then I should see the "granularityCellACL3" datagrid
    And the row 1 of the "granularityCellACL3" datagrid should contain:
      | site   | administrators                                            | details     |
      | Annecy | administrateur.site@toto.com | contributeur.site@toto.com | 1 \| 1 \| 1 |
  # Contenu popup
    When I click "1 | 1 | 1" in the row 1 of the "granularityCellACL3" datagrid
    Then I should see the "cellACLs5" datagrid
    And the row 1 of the "cellACLs5" datagrid should contain:
      | userEmail                    | userRole       |
      | administrateur.site@toto.com | Administrateur |
  # On descend au niveau site
    When I click element ".icon-plus"
    And I click "goTo3"
    And I open tab "Rôles"
    Then I should not see "Site — par élément d'organisation"

  @javascript
  Scenario: Display of collapses of roles by cell for subcells with filters scenario
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Rôles"
    And I open collapse "Site — par élément d'organisation"
    Then I should see the "granularityCellACL3" datagrid
    And the "granularityCellACL3" datagrid should contain 4 row
    When I open collapse "Filtres"
    And I select "Chambéry" from "granularityCellACL3_site_filterForm"
    And I click "Filtrer"
    Then the "granularityCellACL3" datagrid should contain 1 row