@dbFull
Feature: Edit AF translations

  Background:
    Given I am logged in

  @javascript @readOnly
  Scenario: Root groups do not appear in the datagrid displaying components to translate
    Given I am on "af/translate?library=1"
    And I wait for the page to finish loading
    Then I should not see "root_group"
    And I should see "formulaire_test | g_vide"
