@dbFull
Feature: Subforms input feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Subform input scenario : complete input with zero repetition
  # Formulaire des données générales : un seul champ "Chiffre d'affaires"
    Given I am on "af/af/test/id/3"
    And I wait for the page to finish loading
  # Saisie
    And I fill in "sous_formulaire_non_repete__chiffre_affaire" with "10"
  # On commence par ajouter une répétition, juste pour tester l'affichage des messages d'erreur et le taux de complétudi
    And I click "Ajouter"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie incomplète)."
    And the field "sous_formulaire_repete__nature_combustible__1" should have error: "Merci de renseigner ce champ."
    And the field "sous_formulaire_repete__quantite_combustible__1" should have error: "Merci de renseigner ce champ."
    And I should see "33%"
  # Puis on supprime le bloc pour enregistrer une saisie complète
    When I click "Supprimer"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    And I should see "100%"

  @javascript
  Scenario: Subform input scenario : complete input with 1 repetition
  # Formulaire des données générales : un seul champ "Chiffre d'affaires"
    Given I am on "af/af/test/id/3"
    And I wait for the page to finish loading
  # Saisie complète avec 1 répétition
    And I fill in "sous_formulaire_non_repete__chiffre_affaire" with "10"
    And I click "Ajouter"
    And I select "Charbon" from "sous_formulaire_repete__nature_combustible__1"
    And I fill in "sous_formulaire_repete__quantite_combustible__1" with "10"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    And I should see "100%"

  @javascript
  Scenario: Subform input scenario : complete input with 2 repetitions
  # Formulaire des données générales : un seul champ "Chiffre d'affaires"
    Given I am on "af/af/test/id/3"
    And I wait for the page to finish loading
  # Saisie complète avec 2 répétitions
    And I fill in "sous_formulaire_non_repete__chiffre_affaire" with "10"
    And I click "Ajouter"
    And I click "Ajouter"
    And I select "Charbon" from "sous_formulaire_repete__nature_combustible__1"
    And I fill in "sous_formulaire_repete__quantite_combustible__1" with "10"
    And I select "Charbon" from "sous_formulaire_repete__nature_combustible__2"
    And I fill in "sous_formulaire_repete__quantite_combustible__2" with "20"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    And I should see "100%"

