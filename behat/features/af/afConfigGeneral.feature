@dbFull
Feature: AF configuration general tab feature

  Background:
    Given I am logged in

  @javascript
  Scenario: AF configuration general tab scenario
    Given I am on "af/edit/menu/id/3"
    And I wait for the page to finish loading
  # Vérification du contenu des champs
    Then the "label" field should contain "Formulaire vide"
    And the "ref" field should contain "formulaire_vide"
    And the "documentation" field should contain ""
  # Clic sur "Enregistrer", identifiant vide
    When I fill in "label" with "Test"
    And I fill in "ref" with ""
    And I fill in "documentation" with "Blabla"
    And I click "Enregistrer"
    Then the field "ref" should have error: "Merci de renseigner ce champ."
  # Clic sur "Enregistrer", identifiant avec des caractères non autorisés
    When I fill in "ref" with "bépo"
    And I click "Enregistrer"
    Then the field "ref" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Clic sur "Enregistrer", identifiant déjà utilisé
    When I fill in "ref" with "combustion_combustible_unite_masse"
    And I click "Enregistrer"
    Then the field "ref" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Clic sur "Enregistrer", saisie correcte
    When I fill in "ref" with "test"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Vérification du contenu (modifié) des champs
    And the "label" field should contain "Test"
    And the "ref" field should contain "Test"
    And the "documentation" field should contain "Blabla"
  # Clic sur "Quitter", retour au datagrid des formulaires comptables
    When I click "Quitter"
    Then I should see the "listAF" datagrid
