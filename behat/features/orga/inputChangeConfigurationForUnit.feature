@dbFull
Feature: Organization input input feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Change unit of a numeric field while an input already exists, case of a compatible unit
    Given I am on "orga/cell/input/idCell/30/"
    And I wait for the page to finish loading
    Then I should see "Saisie 2012 | Annecy | Énergie"
    And the "quantite_combustible" field should contain "10"
    And the "quantite_combustible_unit" field should contain "t"
    And the "quantite_combustible_unit" field should not contain "g"
    And the "percentquantite_combustible" field should contain "15"
  # Changer l'unité dans l'interface de configuration du champ correspondant, unité compatible
    Given I am on "af/edit/menu/id/1"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs numériques"
    And I set "kg" for column "unit" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
  # Retour à la saisie : la saisie n'a pas été modifiée
    Given I am on "orga/cell/input/idCell/31/"
    And I wait for the page to finish loading
    And the "quantite_combustible" field should contain "10"
    And the "quantite_combustible_unit" field should contain "t"

  @javascript
  Scenario: Change unit of a numeric field while an input already exists, case of a incompatible unit
    Given I am on "af/edit/menu/id/1"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs numériques"
    And I set "m" for column "unit" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
  # Retour à la saisie
    Given I am on "orga/cell/input/idCell/30/"
    And I wait for the page to finish loading
  # Cette fois-ci, la saisie a été supprimée
    Then the "quantite_combustible" field should not contain "10"
    And the "quantite_combustible_unit" field should contain "m"

  @javascript
  Scenario: Deny the ability to change unit of a numeric field while an input already exists scenario and unit has been changed for this input
    Given I am on "orga/cell/input/idCell/30/"
    And I wait for the page to finish loading
    Then I should see "Saisie 2012 | Annecy | Énergie"
    When I fill in "quantite_combustible" with "1000"
    And I select "kg" from "quantite_combustible_unit"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
  # Rendre non modifiable l'unité du champ dans l'interface de configuration
    Given I am on "af/edit/menu/id/1"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Champs numériques"
    And I set "Non modifiable" for column "unitSelection" of row 1 of the "numericFieldDatagrid" datagrid with a confirmation message
  # Retour à la saisie
    Given I am on "orga/cell/input/idCell/31/"
    And I wait for the page to finish loading
    Then the "quantite_combustible_unit" field should contain "t"
    And the "quantite_combustible" field should contain "1"
