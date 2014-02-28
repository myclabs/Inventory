@dbFull
Feature: Switching account

  Background:
    Given I am logged in

  @javascript @readOnly
  Scenario: I can switch account
    Given I am on the dashboard for account 2
    When I switch to account "My C-Sense"
    Then I should see "My C-Sense Dashboard"
