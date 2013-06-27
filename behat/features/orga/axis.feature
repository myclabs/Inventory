@dbOneOrganization
Feature: OrgaAxesNOK

  Background:
    Given I am logged in

  @javascript
  Scenario: OrgaAxesEdit
    # Accès à l'onglet "Axes"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Organisation"
    And I open tab "Axes"
    # Ajout d'un axe, identifiant vide
    And I follow "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I press "Valider"
    And I wait for the page to finish loading
    Then the field "addAxis_ref" should have error: "Merci de renseigner ce champ."
    # Ajout d'un axe, caractères non autorisés

