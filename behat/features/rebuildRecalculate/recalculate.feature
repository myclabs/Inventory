@dbFull
Feature: Rebuild feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Simple recalculate for workspace
  # Accès à l'onglet "Reconstruction"
    Given I am on "orga/workspace/edit/workspace/1"
    And I wait for the page to finish loading
    And I open tab "Reconstruction"
    And I wait 2 seconds
    Then I should see "Régénération des données d'analyse"
  # Régénération simple du workspace global
    When I click "Relancer les calculs et régénérer les données d'analyse"
    And I wait 15 seconds
    Then the following message is shown and closed: "Opération en cours."

  @javascript
  Scenario: Check attribute finished of an input
  # Aller consulter une saisie terminée
    Given I am on "orga/cell/view/cell/1"
    And I wait for the page to finish loading
  # Cliquer sur le bouton "Réinitialiser" pour la granularité Année|Site, pour faire apparaître les collectes clôturées
  # Remarque : c'est juste pour récupérer un accès au statut fait ailleurs, dans l'absolu pas de raison d'aller chercher
  # une saisie clôturée…
    And I click element "div[id='granularity8'] button.reset"
  # Vérification contenu datagrid
    Then the "/1-annee:1-2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/" cell input status should be "statusFinished"
  # Accès à l'onglet "Reconstruction"
    When I am on "orga/workspace/edit/workspace/1"
    And I wait for the page to finish loading
    And I open tab "Reconstruction"
    And I wait 2 seconds
    Then I should see "Régénération des données d'analyse"
  # Régénération simple du workspace global
    When I click "Relancer les calculs et régénérer les données d'analyse"
    And I wait 15 seconds
    Then the following message is shown and closed: "Opération en cours."
  # Retour à la saisie précédente, et vérification que le statut n'a pas été modifié
    When I am on "orga/cell/view/cell/1"
    And I wait for the page to finish loading
  # Cliquer sur le bouton "Réinitialiser" pour la granularité Année|Site, pour faire apparaître les collectes clôturées
    And I click element "div[id='granularity8'] button.reset"
  # Vérification contenu datagrid
    Then the "/1-annee:1-2012/&/1-zone:europe/1-pays:france/2-site:grenoble/&/2-marque:marque_b/2-site:grenoble/" cell input status should be "statusFinished"
