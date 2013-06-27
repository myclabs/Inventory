@dbOneOrganization
Feature: OrganizationsNOK

  Background:
    Given I am logged in

  @javascript
  Scenario: OrgaLink
    # Affichage et contenu du datagrid
    Then I should see the "organizations" datagrid
    # Then I wait for 10 seconds
    Then the row 1 of the "organizations" datagrid should contain:
      | label      | details   | delete |
      | Organisation test   | Détails   | Supprimer  |
    When I follow "Détails"
    And I wait for the page to finish loading
    Then I should see "Unité organisationnelle globale Organisation test"

  @javascript
  Scenario: OrgaEdit
    Then I should see the "organizations" datagrid
    # Lien "Détails"
    When I follow "Détails"
    And I wait for the page to finish loading
    # Affichage onglet "Configuration"
    When I follow "Organisation"
    And I follow "Configuration"
    # Modification du libellé
    And I fill in "Libellé" with "Organisation test modifiee"
    And I press "Enregistrer"
    Then I should see "Ok : Modification effectuée."
    When I press "x"
    Then I should not see "Ok : Modification effectuée."
    And I should see "Unité organisationnelle globale Organisation test modifiee"
    # Test bouton statut données d'analyse (données vides)
    When I press "Tester si la structure des données d'analyse est à jour"
    Then I should see "La structure des données d'analyse est à jour"
    # Retour à la page des organisations
    When I press "Accueil"
    And I wait for the page to finish loading
    Then I should see the "organizations" datagrid
    # Ajout d'une organisation
    When I follow "Ajouter"
    Then I should see the popup "Ajout d'une organisation"
    When I fill in "Libellé" with "Organisation-a-supprimer"
    When I press "Valider"
    And I wait for the page to finish loading
    Then I should see "Ok : Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
    When I press "x"
    Then I should not see "Ok : Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps."
