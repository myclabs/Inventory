@dbEmpty
Feature: orgaOrga

  Background:
    Given I am logged in

  @javascript
  Scenario: orgaOrga1
  # Ajout d'une organisation
    When I am on "orga/organization/manage"
    Then I should see the "organizations" datagrid
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une organisation"
    When I fill in "Libellé" with "Organisation test"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
    And the row 1 of the "organizations" datagrid should contain:
      | label      | details   | delete |
      | Organisation test   | Détails   | Supprimer  |
  # Lien "Détails"
    When I click "Détails"
    Then I should see "Unité organisationnelle globale Organisation test"
  # Accès à l'onglet "Configuration"
    When I open tab "Organisation"
    And I open tab "Configuration"
    Then I should see "Niveau organisationnel des inventaires"
  # Modification du libellé
    When I fill in "Libellé" with "Organisation test modifiee"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Test bouton statut données d'analyse (données vides)
    When I click "Tester si la structure des données d'analyse est à jour"
    Then I should see "La structure des données d'analyse de l'organisation est à jour"
  # Retour à la page d'accueil
    # When I click "Accueil"
    # When I click "ul.nav a:contains('Accueil')"
    When I am on "orga/organization/manage"
    Then I should see the "organizations" datagrid
    And the row 1 of the "organizations" datagrid should contain:
      | label      | details   | delete |
      | Organisation test modifiee  | Détails   | Supprimer  |
  # Suppression d'une organisation (organisation vide)
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
  # Pour l'instant la suppression ne fonctionne pas

  @javascript
  Scenario: orgaOrga2
  # Ajout d'une organisation
    When I am on "orga/organization/manage"
    Then I should see the "organizations" datagrid
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une organisation"
    When I fill in "Libellé" with "Organisation test"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
  # Accès au sous-onglet "Granularités"
    When I click "Détails"
    And I open tab "Organisation"
    And I open tab "Niveaux"
    Then I should see the "granularity" datagrid
    And the row 1 of the "granularity" datagrid should contain:
      | axes  | navigable  | orgaTab | aCL | aFTab | dW  |
      |       | Navigable  | Oui     | Oui | Oui   | Oui |