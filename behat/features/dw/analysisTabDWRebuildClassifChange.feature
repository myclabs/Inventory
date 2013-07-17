@dbForTestDWUpToDate
Feature: Analysis data rebuild after a change in classification data feature (analysis tab)

  Background:
    Given I am logged in

  @javascript
  Scenario: Analysis data rebuild after editing classification axes
  # Accès à l'onglet "Analyses"
    Given I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
  # Au départ les données d'analyse sont à jour
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Ajout axe
    When I am on "classif/axis/manage"
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un axe"
    When I fill in "label" with "Test"
    And I fill in "ref" with "test"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification du libellé d'un axe
    When I am on "classif/axis/manage"
    And I wait 5 seconds
    And I click "Test"
    Then I should see the popup "Édition d'un axe"
    When I fill in "editAxis_label" with "Test modifié"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification de l'identifiant d'un axe
    When I am on "classif/axis/manage"
    And I wait 5 seconds
    And I click "Test modifié"
    Then I should see the popup "Édition d'un axe"
    When I fill in "editAxis_ref" with "test_modifie"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification de l'axe plus grossier direct d'un axe (déplacement à la racine)
    When I am on "classif/axis/manage"
    And I wait 7 seconds
    And I click "Scope"
    Then I should see the popup "Édition d'un axe"
    When I select "Aucun" from "editAxis_changeParent"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification de la position (numéro d'ordre) d'un axe (modification non détectée)
    When I am on "classif/axis/manage"
    And I wait 5 seconds
    And I click "Test modifié"
    Then I should see the popup "Édition d'un axe"
    When I check "Premier"
    And I click "Confirmer"
    Then the following message is shown and closed: "Modification effectuée."
  # Détection modification : la modification n'est pas détectée (normal)
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Suppression axe
    When I am on "classif/axis/manage"
    And I wait 5 seconds
    And I click "Test modifié"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Détection axe organisationnel supprimé
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."

  @javascript
  Scenario: Analysis data rebuild after editing classification members
    Given I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
  # Au départ les données d'analyse sont à jour
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Ajout d'un membre
    When I am on "classif/member/manage"
    And I wait 5 seconds
    And I open collapse "Poste article 75"
    And I click "Ajouter"
    Then I should see the popup "Ajout d'un membre à l'axe « Poste article 75 »"
    When I fill in "membersposte_article_75_label_addForm" with "Test"
    And I fill in "membersposte_article_75_ref_addForm" with "test"
    And I select "1" from "membersposte_article_75_broaderscope_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification du libellé d'un membre
    When I am on "classif/member/manage"
    And I wait 5 seconds
    And I open collapse "Poste article 75"
    When I set "Test modifié" for column "label" of row 1 of the "membersposte_article_75" datagrid with a confirmation message
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification de l'identifiant d'un membre
    When I am on "classif/member/manage"
    And I wait 5 seconds
    And I open collapse "Poste article 75"
    When I set "test_modifie" for column "ref" of row 1 of the "membersposte_article_75" datagrid with a confirmation message
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification membre parent d'un membre
    When I am on "classif/member/manage"
    And I wait 5 seconds
    And I open collapse "Poste article 75"
    When I set "2" for column "broaderscope" of row 1 of the "membersposte_article_75" datagrid with a confirmation message
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Suppression d'un membre
    When I am on "classif/member/manage"
    And I wait 5 seconds
    And I open collapse "Poste article 75"
    And I click "Supprimer" in the row 1 of the "membersposte_article_75" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."

  @javascript
  Scenario: Analysis data rebuild after editing classification indicators
    Given I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
  # Au départ les données d'analyse sont à jour
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Ajout d'un indicateur
    When I am on "classif/indicator/manage"
    And I wait for the page to finish loading
    Then I should see the "editIndicators" datagrid
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un indicateur"
    When I fill in "editIndicators_label_addForm" with "Test"
    And I fill in "editIndicators_ref_addForm" with "test"
    And I fill in "editIndicators_unit_addForm" with "t"
    And I fill in "editIndicators_ratioUnit_addForm" with "t"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification du libellé d'un indicateur
    When I am on "classif/indicator/manage"
    And I wait for the page to finish loading
    And I set "Test modifié" for column "label" of row 2 of the "editIndicators" datagrid with a confirmation message
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification de l'identifiant d'un indicateur
    When I am on "classif/indicator/manage"
    And I wait for the page to finish loading
    And I set "test_modifie" for column "ref" of row 2 of the "editIndicators" datagrid with a confirmation message
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # TODO : Modification du numéro d'ordre d'un indicateur
  # Modification de l'unité d'un indicateur
  # Modification de l'identifiant d'un indicateur
    When I am on "classif/indicator/manage"
    And I wait for the page to finish loading
    And I set "kg" for column "unit" of row 2 of the "editIndicators" datagrid with a confirmation message
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Modification de l'unité pour ratio d'un indicateur
    When I am on "classif/indicator/manage"
    And I wait for the page to finish loading
    And I set "kg" for column "ratioUnit" of row 2 of the "editIndicators" datagrid with a confirmation message
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Suppression d'un indicateur
    When I am on "classif/indicator/manage"
    And I wait for the page to finish loading
    And I click "Supprimer" in the row 2 of the "editIndicators" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Détection modification
    When I am on "orga/cell/details/idCell/1/tab/analyses"
    And I wait for the page to finish loading
    Then I should see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."
  # Régénération
    When I click "Régénérer les données d'analyse"
    Then the following message is shown and closed: "Opération en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Le résultat sera visible au plus tard dans quelques minutes."
    When I reload the page
    And I wait for the page to finish loading
    Then I should not see "Les données de structure du cube d'analyse (axes, membres, indicateurs) ne sont plus à jour."