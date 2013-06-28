@dbOneOrganization
Feature: OrgaAxis

  Background:
    Given I am logged in

  @javascript
  Scenario: OrgaAxisEdit
  # Accès à l'onglet "Axes"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Organisation"
    And I open tab "Axes"
  # Ajout d'un axe, identifiant vide
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I click "Valider"
    Then the field "addAxis_ref" should have error: "Merci de renseigner ce champ."
  # Ajout d'un axe, caractères non autorisés
    When I fill in "addAxis_ref" with "bépo"
    When I click "Valider"
    Then the field "addAxis_ref" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout d'un axe, saisie correcte
    When I fill in "addAxis_label" with "À supprimer"
    When I fill in "addAxis_ref" with "a_supprimer"
    When I click "Valider"
    Then the following message is shown and closed: "Ajout effectué"
  # Suppression d'un axe
