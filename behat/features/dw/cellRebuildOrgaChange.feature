@dbFull
Feature: Régénération des données d'analyse

  Background:
    Given I am logged in

  @javascript
  Scenario: Rebuild of analysis data after editing organizational axes
    Given I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
  # Au départ les données d'analyse sont à jour
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Ajout axe
    When I open tab "Organisation"
    And I open tab "Axes"
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Test rebuild"
    And I fill in "addAxis_ref" with "test_rebuild"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification du libellé d'un axe
    When I open tab "Organisation"
    And I open tab "Axes"
    And I click "Test rebuild"
    Then I should see the popup "Édition d'un axe"
    When I fill in "editAxis_label" with "Test rebuild modifié"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification de l'identifiant d'un axe
    When I open tab "Organisation"
    And I open tab "Axes"
    And I click "Test rebuild modifié"
    Then I should see the popup "Édition d'un axe"
    When I fill in "editAxis_ref" with "test_rebuild_modifie"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification de la position (numéro d'ordre) d'un axe
    When I open tab "Organisation"
    And I open tab "Axes"
    And I click "Test rebuild modifié"
    Then I should see the popup "Édition d'un axe"
    When I check "Premier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Suppression axe
    When I open tab "Organisation"
    And I open tab "Axes"
    And I click "Test rebuild modifié"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Détection axe organisationnel supprimé
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."

  @javascript
  Scenario: Rebuild of analysis data after editing organizational members
    Given I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
  # Au départ les données d'analyse sont à jour
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Ajout membre
    When I open tab "Organisation"
    And I open collapse "Site"
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un membre à l'axe « Site »"
    When I fill in "listMemberssite_label_addForm" with "Annecy"
    And I fill in "listMemberssite_ref_addForm" with "annecy"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
  # Ajout d'un second membre pour modification parent premier membre
    When I open collapse "Pays"
    And I click element "#memberspays .btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un membre à l'axe « Pays »"
    When I fill in "listMemberspays_label_addForm" with "France"
    And I fill in "listMemberspays_ref_addForm" with "france"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification du libellé d'un membre
    When I open tab "Organisation"
    And I open collapse "Site"
    When I set "Annecy modifié" for column "label" of row 1 of the "listMemberssite" datagrid with a confirmation message
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification de l'identifiant d'un membre
    When I open tab "Organisation"
    And I open collapse "Site"
    When I set "annecy_modifie" for column "ref" of row 1 of the "listMemberssite" datagrid with a confirmation message
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification du membre parent d'un membre
    When I open tab "Organisation"
    And I open collapse "Site"
    When I set "france#" for column "broaderpays" of row 1 of the "listMemberssite" datagrid with a confirmation message
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Suppression membre
    When I open tab "Organisation"
    And I open collapse "Site"
    And I click "Supprimer" in the row 1 of the "listMemberssite" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."

