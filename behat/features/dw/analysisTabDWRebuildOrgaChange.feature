@dbForTestDWUpToDate
Feature: Analysis data rebuild after a change in organizational data feature (analysis tab)

  Background:
    Given I am logged in

  @javascript
  Scenario: Analysis data rebuild after editing organizational axes
  # Accès à l'onglet
    Given I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
  # Au départ les données d'analyse sont à jour
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Ajout axe
    When I open tab "Organisation"
    And I open tab "Axes"
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "addAxis_label" with "Test"
    And I fill in "addAxis_ref" with "test"
    And I click "Valider"
    And I wait 3 seconds
    Then the following message is shown and closed: "Ajout effectué."
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Régénération des données d'analyse effectuée."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification du libellé d'un axe
    When I open tab "Organisation"
    And I open tab "Axes"
    And I click "Test"
    Then I should see the popup "Édition d'un axe"
    When I fill in "editAxis_label" with "Test modifié"
    And I click "Confirmer"
    And I wait 5 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Régénération des données d'analyse effectuée."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification de l'identifiant d'un axe
    When I open tab "Organisation"
    And I open tab "Axes"
    And I click "Test modifié"
    Then I should see the popup "Édition d'un axe"
    When I fill in "editAxis_ref" with "test_modifie"
    And I click "Confirmer"
    And I wait 5 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Régénération des données d'analyse effectuée."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification de la position (numéro d'ordre) d'un axe : modification non détectée
    When I open tab "Organisation"
    And I open tab "Axes"
    And I click "Test modifié"
    Then I should see the popup "Édition d'un axe"
    When I check "Premier"
    And I click "Confirmer"
    And I wait 5 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Détection modification (modification non détectée, OK)
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Suppression axe
    When I open tab "Organisation"
    And I open tab "Axes"
    And I click "Test modifié"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    And I wait 5 seconds
    Then the following message is shown and closed: "Suppression effectuée."
  # Détection axe organisationnel supprimé
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Régénération des données d'analyse effectuée."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."

  @javascript
  Scenario: Analysis data rebuild after editing organizational members
    Given I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
  # Au départ les données d'analyse sont à jour
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Ajout membre (membre parent non renseigné)
    When I open tab "Organisation"
    And I open collapse "Site"
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un membre à l'axe « Site »"
    When I fill in "listMemberssite_label_addForm" with "Test"
    And I fill in "listMemberssite_ref_addForm" with "test"
  # And I fill in "listMemberssite_broaderpays_addForm" with "france#"
    And I click "Valider"
    And I wait 5 seconds
    Then the following message is shown and closed: "Ajout effectué."
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Régénération des données d'analyse effectuée."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification du libellé d'un membre
    When I open tab "Organisation"
    And I open collapse "Site"
    When I set "Test modifié" for column "label" of row 1 of the "listMemberssite" datagrid with a confirmation message
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Régénération des données d'analyse effectuée."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification de l'identifiant d'un membre
    When I open tab "Organisation"
    And I open collapse "Site"
    When I set "test_modifie" for column "ref" of row 1 of the "listMemberssite" datagrid with a confirmation message
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Régénération des données d'analyse effectuée."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Ajout membre parent à un membre
    When I open tab "Organisation"
    And I open collapse "Site"
    When I set "france#" for column "broaderpays" of row 1 of the "listMemberssite" datagrid
    And I wait 5 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Régénération des données d'analyse effectuée."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification membre parent d'un membre
    When I open tab "Organisation"
    And I open collapse "Site"
    When I set "italie#" for column "broaderpays" of row 1 of the "listMemberssite" datagrid
    And I wait 5 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Régénération des données d'analyse effectuée."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Suppression membre parent d'un membre
    When I open tab "Organisation"
    And I open collapse "Site"
    When I set "" for column "broaderpays" of row 1 of the "listMemberssite" datagrid
    And I wait 5 seconds
    Then the following message is shown and closed: "Modification effectuée."
  # Détection modification
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Régénération des données d'analyse effectuée."
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
    Then the following message is shown and closed: "Régénération des données d'analyse effectuée."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."

