@dbEmpty
Feature: Cell form tab edition feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Send a feedback message
    Given I am on the homepage
    And I click "Aidez-nous à améliorer cette page"
    And I click "Annuler"
    And I click "Aidez-nous à améliorer cette page"
    And I check "Le contenu de cette page n'est pas clair"
    And I check "Je constate un dysfonctionnement"
    And I check "J'ai une amélioration à proposer"
    And I fill in "details" with "Test automatique behat."
    And I click "Envoyer"
    # Then the following message is shown and closed: "Votre retour a été enregistré, merci."
    Then I should see "Votre retour a été enregistré, merci."
