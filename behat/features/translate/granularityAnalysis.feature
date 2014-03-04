@dbFull
Feature: Edit granularity analysis label translations feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Edit granularity analysis label translations scenario
  # Accès au datagrid des analyses préconfigurées au niveau global
    Given I am on "orga/translate/granularityreports/idOrganization/1"
    And I wait for the page to finish loading
    Then I should see "Analyses préconfigurées Traductions"
    When I open collapse "Niveau organisationnel global"
    Then I should see the "datagridTranslate_DW_Model_Report_label_1" datagrid
    And the row 1 of the "datagridTranslate_DW_Model_Report_label_1" datagrid should contain:
      | fr                           |
      | Chiffre d'affaire, par année |
    And the row 2 of the "datagridTranslate_DW_Model_Report_label_1" datagrid should contain:
      | fr                                               |
      | Chiffre d'affaire 2012, marques A et B, par site |
    When I set "Chiffre d'affaire, par année (modifié)" for column "fr" of row 1 of the "datagridTranslate_DW_Model_Report_label_1" datagrid
    And I set "Oh my god" for column "en" of row 1 of the "datagridTranslate_DW_Model_Report_label_1" datagrid
    Then the row 1 of the "datagridTranslate_DW_Model_Report_label_1" datagrid should contain:
      | fr                                    | en        |
      | Chiffre d'affaire, par année (modifié)| Oh my god |
  # Accès au datagrid des analyses préconfigurées au niveau "Site"
    When I close collapse "Niveau organisationnel global"
    And I open collapse "Site"
    Then I should see the "datagridTranslate_DW_Model_Report_label_4" datagrid
    And the row 1 of the "datagridTranslate_DW_Model_Report_label_4" datagrid should contain:
      | fr                           |
      | Chiffre d'affaire, par année |