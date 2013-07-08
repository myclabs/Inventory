@dbFull
Feature: Keywords

  Background:
    Given I am logged in

  @javascript
  Scenario: Keywords Graph feature
    Given I am on "keyword/graph/root"
    Then I should see "Graphe des mots clés"
    And I should see "charbon (charbon)"
  # Clic sur un mot clé
    When I click "charbon (charbon)"
    Then I should see "Sujets"
  # Retour aux mots clés racines
    When I click "Aller aux mots clés racines"
    Then I should see "Mots clés racines"
  # Utilisation du champ "Aller à", saisie incorrecte
    When I fill in "keywordGoTo" with "auie"
    And I click "OK"
    Then the following message is shown and closed: "Le contenu du champ « Aller à » n'a mené à aucun mot clé."
    And I should see "Mots clés racines"
  # Utilisation du champ "Aller à", saisie correcte
    When I fill in "keywordGoTo" with "charbon"
    And I click "OK"
    Then I should see "Sujets"