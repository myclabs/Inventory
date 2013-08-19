@dbFull
Feature: Edit AF translations

  Background:
    Given I am logged in

  @javascript
  Scenario: Root groups do not appear in the datagrid displaying components to translate
    Given I am on "af/translate/components-label"
    And I wait for the page to finish loading
    Then I should not see "root_group"
    And I should see "formulaire_test | g_vide"