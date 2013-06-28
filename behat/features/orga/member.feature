@dbOneOrganizationWithAxes
Feature: OrgaMembers

  Background:
    Given I am logged in

  # @javascript
  Scenario: OrgaMembersNOKScenario
    # Accès à l'onglet "Membres"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Organisation"
    And I open tab "Membres"
    Then I should see "Année"
    # Déplier un volet
    When I follow "Année"
    Then I should see the "listMembersannee" datagrid
    # Ajout d'un membre, identifiant vide
    When I follow "Ajouter"
    Then I should see the popup "Ajout d'un membre à l'axe « Année »"
    When I press "Valider"
    And I wait for the page to finish loading
    Then the field "listMembersannee_ref_addForm" should have error: "Merci de renseigner ce champ."
    # Ajout d'un membre, identifiant avec caractères non autorisés
    When I fill in "Identifiant" with "bépo"
    And I press "Valider"
    And I wait for the page to finish loading
    Then the field "listMembersannee_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
    # Ajout d'un membre, saisie correcte
    When I fill in "Libellé" with "À supprimer"
    And I fill in "Identifiant" with "a_supprimer"
    And I wait for the page to finish loading
    Then I should see "Ok : Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
    When I press "x"
    Then I should not see "Ok : Ajout en cours. En fonction des données présentes"
    # Suppression d'un membre, sans obstacle
    When I follow "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I press "Confirmer"
    And I wait for the page to finish loading
    Then I should see "Ok : Suppression effectuée."
    When I press "x"
    Then I should not see "Ok : Suppression effectuée."


