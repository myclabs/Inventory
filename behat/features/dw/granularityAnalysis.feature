@dbFull
Feature: Granularity dataware analysis feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Trying to launch an analysis when configuration is not complete
  # Affichage des messages d'erreur lorsque des champs ne sont pas remplis
  # Accès à l'onglet "Informations générales"
    Given I am on "orga/workspace/edit/workspace/1"
    And I open tab "Config. Analyses"
  # Accès au datagrid des analyses pré-configurées au niveau global
    And I open collapse "Niveau organisationnel global"
    Then I should see the "datagridCellReports1" datagrid
  # Nouvelle analyse
    When I click element "#cellReports1 a:contains('Ajouter')"
  # Tentative de lancement, sans avoir rien saisi
    And I click "Lancer"
    Then the field "typeSumRatioChoice" should have error: "Merci de préciser la nature des valeurs à fournir."
    And the field "displayType" should have error: "Merci de préciser le type de graphique à afficher."
  # Tentative de lancement sans avoir précisé l'indicateur
    When I select "sum" from "typeSumRatioChoice"
    And I select "Camembert" from "displayType"
    And I click "Lancer"
  # Si le dataware comprend un indicateur il est indiqué par défaut
    Then the following message is shown and closed: "Analyse effectuée."
  # Sélection "Ratio"
    When I click element "input[name='typeSumRatioChoice'][value='ratio']"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
    When I click element "input[name='ratioAxisNumberChoice'][value='two']"
    And I click "Lancer"
    Then the field "displayType" should have error: "Merci de préciser le type de graphique à afficher."
    When I select "Histogramme vertical groupé" from "displayType"
    And I click "Lancer"
    Then the field "ratioNumeratorAxisTwo" should have error: "Merci de choisir deux axes différents."
    When I select "Pays" from "ratioNumeratorAxisTwo"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
  # Retour
    When I click "Retour"
    Then I should see "Workspace avec données"

  @javascript
  Scenario: Display of the status of analysis configuration (new configuration / change in course)
  # Affichage du cartouche indiquant le statut de la configuration
    Given I am on "orga/granularity/view-report/granularity/1/"
  # Nouvelle analyse
    Then I should see "Nouvelle configuration"
    And I should not see "Modifications en cours"
  # Clic sur "Indicateur"
    When I select "sum" from "typeSumRatioChoice"
    Then I should not see "Nouvelle configuration"
    And I should see "Modifications en cours"
  # Réinitialisation
    When I click "Réinitialiser"
    Then I should see "Nouvelle configuration"
    And I should not see "Modifications en cours"
  # Clic sur "Ratio"
    When I click element "input[name='typeSumRatioChoice'][value='ratio']"
    Then I should not see "Nouvelle configuration"
    And I should see "Modifications en cours"
  # Réinitialisation
    When I click "Réinitialiser"
    Then I should see "Nouvelle configuration"
    And I should not see "Modifications en cours"
  # Clic sur la case à cocher "Afficher l'incertitude"
    When I click element "input[name='uncertaintyChoice']"
    Then I should not see "Nouvelle configuration"
    And I should see "Modifications en cours"
  # Réinitialisation
    When I click "Réinitialiser"
    Then I should see "Nouvelle configuration"
    And I should not see "Modifications en cours"
  # Édition d'un filtre
    When I open collapse "Filtres"
    And I select "o_annee" from "addFilter"
    And I click element "button[class='btn btn-default add-filter']"
    And I click element "input[name='o_annee_memberNumberChoice'][value='one']"
      # Focus pour scroller vers le haut
    And I focus on element ".btn:contains('Retour')"
    Then I should not see "Nouvelle configuration"
    And I should see "Modifications en cours"

  @javascript
  Scenario: Launch and save a granularity analysis, empty label
  # Accès à l'interface de configuration d'une analyse
    Given I am on "orga/granularity/view-report/granularity/1/"
    When I select "sum" from "typeSumRatioChoice"
    And I select "Camembert" from "displayType"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
  # Enregistrement de l'analyse préconfigurée, libellé vide
    When I click "Enregistrer"
    Then I should see the popup "Enregistrer la configuration de l'analyse"
    When I click element "#saveReport .btn:contains('Enregistrer')"
    Then the field "reportLabel" should have error: "La configuration n'a pas pu être enregistrée, car le libellé saisi est vide."

  @javascript
  Scenario: Launch and save a granularity analysis, non empty label
  # Accès à l'interface de configuration d'une analyse
    Given I am on "orga/granularity/view-report/granularity/1/"
    And I wait for the page to finish loading
    When I select "sum" from "typeSumRatioChoice"
    And I select "Camembert" from "displayType"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
  # Enregistrement de l'analyse préconfigurée, libellé non vide
    When I click "Enregistrer"
    Then I should see the popup "Enregistrer la configuration de l'analyse"
    When I fill in "Libellé" with "Analyse préconfigurée test"
    And I click element "#saveReport .btn:contains('Enregistrer')"
    Then I should see "Analyse préconfigurée test Niveau organisationnel global"
    And I should see "Configuration enregistrée"
  # clic sur l'onglet "Valeurs", histoire de
    When I open tab "Valeurs"
    Then I should see the "reportValues" datagrid
    And the "reportValues" datagrid should contain 0 row
  # Retour à la liste des analyses préconfigurées
    When I click "Retour"
  # Vérification que l'analyse apparaît bien parmi les analyses préconfigurées
    And I open collapse "Niveau organisationnel global"
    Then I should see the "datagridCellReports1" datagrid
    And the "datagridCellReports1" datagrid should contain a row:
      | report                     |
      | Analyse préconfigurée test |
  # Accès à l'analyse préconfigurée enregistrée
    When I click "Cliquer pour accéder" in the row 1 of the "datagridCellReports1" datagrid
    Then I should see "Analyse préconfigurée test Niveau organisationnel global"
  # Vérification que l'analyse préconfigurée est bien présente dans la cellule globale
    When I click "Retour"
    And I open collapse "Niveau organisationnel global"
    Then I should see the "datagridCellReports1" datagrid
    And the "datagridCellReports1" datagrid should contain a row:
      | report                      |
      | Analyse préconfigurée test |
    And the row 1 of the "datagridCellReports1" datagrid should contain:
    # And the row 3 of the "report" datagrid should contain:
      | report                      |
      | Analyse préconfigurée test |
  # Accès à l'analyse de la cellule
    When I click "Données"
    And I click element "#currentGranularity .fa-bar-chart-o"
    And I click "Analyse préconfigurée test"
    Then I should see "Analyse préconfigurée test Vue globale"

  @javascript
  Scenario: Update a granularity analysis, without any change except on filters
    Given I am on "orga/granularity/view-report/granularity/1/report/2"
    And I wait for the page to finish loading
    Then I should see "Chiffre d'affaire, par année Niveau organisationnel global"
  # Ajout d'un filtre
    When I open collapse "Filtres"
    And I select "o_annee" from "addFilter"
    And I click element "button[class='btn btn-default add-filter']"
    And I click element "input[name='o_annee_memberNumberChoice'][value='one']"
    And I select "2013" from "o_annee_members[]"
    # Focus pour scroller vers le haut
    And I focus on element ".btn:contains('Retour')"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
    When I click element "#saveReportButton.btn:contains('Enregistrer')"
    Then I should see the popup "Enregistrer la configuration de l'analyse"
  # Par défaut, il est proposé de mettre à jour la configuration existante
    When I click element "#saveReport .btn:contains('Enregistrer')"
  # Vérification que le filtre sur 2013 est bien présent sur les analyses de la ou des cellule(s) correspondant à cette granularité
    And I click "Retour"
    And I open collapse "Niveau organisationnel global"
    # Then the row 1 of the "report" datagrid should contain:
    Then the row 2 of the "datagridCellReports1" datagrid should contain:
      | report                       |
      | Chiffre d'affaire, par année |
    When I click "Données"
    And I click element "#currentGranularity .fa-bar-chart-o"
    And I click "Chiffre d'affaire, par année"
    And I open tab "Valeurs"
    Then the "reportValues" datagrid should contain 1 row
    And the row 1 of the "reportValues" datagrid should contain:
      | valueAxiso_annee |
      | 2013             |

  @javascript
  Scenario: Create a granularity analysis, modify it, and save as another configuration scenario
  # Configuration et lancement d'une nouvelle analyse préconfigurée
    Given I am on "orga/granularity/view-report/granularity/1/"
    When I select "sum" from "typeSumRatioChoice"
    And I select "Camembert" from "displayType"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
  # Enregistrement de l'analyse préconfigurée
    When I click "Enregistrer"
    Then I should see the popup "Enregistrer la configuration de l'analyse"
    When I fill in "Libellé" with "Analyse préconfigurée test"
    And I click element "#saveReport .btn:contains('Enregistrer')"
    Then I should see "Analyse préconfigurée test Niveau organisationnel global"
  # Modification de l'analyse (dans la même interface) et lancement
    When I select "Histogramme vertical" from "displayType"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
  # Enregistrement comme une nouvelle analyse
    When I click "Enregistrer"
    Then I should see the popup "Enregistrer la configuration de l'analyse"
    When I click element "label:contains('Créer une nouvelle configuration')"
    And I fill in "Libellé" with "Analyse préconfigurée modifiée test"
    And I click element "#saveReport .btn:contains('Enregistrer')"
    Then I should see "Analyse préconfigurée modifiée test Niveau organisationnel global"
      # Retour à la liste des analyses préconfigurées
    When I click "Retour"
      # Vérification que l'analyse apparaît bien parmi les analyses préconfigurées
    And I open collapse "Niveau organisationnel global"
    Then I should see the "datagridCellReports1" datagrid
    And the "datagridCellReports1" datagrid should contain a row:
      | report                     |
      | Analyse préconfigurée test |
    And the "datagridCellReports1" datagrid should contain a row:
      | report                     |
      | Analyse préconfigurée modifiée test |





















