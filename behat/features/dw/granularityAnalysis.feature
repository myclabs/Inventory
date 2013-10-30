@dbFull
Feature: Granularity dataware analysis feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Trying to launch an analysis when configuration is not complete
  # Affichage des messages d'erreur lorsque des champs ne sont pas remplis
  # Accès à l'onglet "Informations générales"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Organisation"
    And I open tab "Informations générales"
  # Accès au datagrid des analyses pré-configurées au niveau global
    And I open collapse "Niveau organisationnel global"
    Then I should see the "granularity1Report" datagrid
  # Nouvelle analyse
    When I click "Nouvelle analyse"
  # Tentative de lancement, sans avoir rien saisi
    And I click "Lancer"
    Then the field "indicatorRatio" should have error: "Merci de préciser la nature des valeurs à fournir."
    And the field "chartType" should have error: "Merci de préciser le type de graphique à afficher."
  # Tentative de lancement sans avoir précisé l'indicateur
    When I click element "#indicatorRatio_indicator"
    And I select "Camembert" from "chartType"
    And I click "Lancer"
  # Si le dataware comprend un indicateur il est indiqué par défaut
    Then the following message is shown and closed: "Analyse effectuée."
  # Si le dataware ne comprend aucun indicateur l'erreur ci-dessous se produit
  # Then the field "indicator" should have error: "Merci de sélectionner un indicateur."
  # Sélection "Ratio"
    When I click element "#indicatorRatio_ratio"
    And I click "Lancer"
    Then the field "chartType" should have error: "Merci de préciser le type de graphique à afficher."
  # Si le dataware comprend un indicateur il est indiqué par défaut
    When I select "Camembert" from "chartType"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
  # Si le dataware ne comprend aucun indicateur l'erreur ci-dessous se produit
  # Then the field "numerator" should have error: "Merci de sélectionner un indicateur pour le numérateur."
  # And the field "denominator" should have error: "Merci de sélectionner un indicateur pour le dénominateur."
  # Retour
    When I click "Retour"
    Then I should see "Vue globale Organisation avec données"

  @javascript
  Scenario: Display of the status of analysis configuration (new configuration / change in course)
  # Affichage du cartouche indiquant le statut de la configuration
    Given I am on "orga/granularity/report/idCell/1/idGranularity/1/idCube/1"
  # Nouvelle analyse
    Then I should see "Nouvelle configuration"
    And I should not see "Modifications en cours"
  # Clic sur "Indicateur"
    When I click element "#indicatorRatio_indicator"
    Then I should not see "Nouvelle configuration"
    And I should see "Modifications en cours"
  # Réinitialisation
    When I click element "#resetReportConfiguration"
    Then I should see "Nouvelle configuration"
    And I should not see "Modifications en cours"
  # Clic sur "Ratio"
    When I click element "#indicatorRatio_indicator"
    Then I should not see "Nouvelle configuration"
    And I should see "Modifications en cours"
  # Réinitialisation
    When I click element "#resetReportConfiguration"
    Then I should see "Nouvelle configuration"
    And I should not see "Modifications en cours"
  # Sélection du type de graphique
    When I select "Camembert" from "chartType"
    Then I should not see "Nouvelle configuration"
    And I should see "Modifications en cours"
  # Réinitialisation
    When I click element "#resetReportConfiguration"
    Then I should see "Nouvelle configuration"
    And I should not see "Modifications en cours"
  # Clic sur la case à cocher "Afficher l'incertitude"
    When I click element "#withUncertainty"
    Then I should not see "Nouvelle configuration"
    And I should see "Modifications en cours"
  # Réinitialisation
    When I click element "#resetReportConfiguration"
    Then I should see "Nouvelle configuration"
    And I should not see "Modifications en cours"
  # Édition d'un filtre
    When I open collapse "Filtres"
    And I click element "#filterAxiso_anneeNumberMembers_one"
    Then I should not see "Nouvelle configuration"
    And I should see "Modifications en cours"

  @javascript
  Scenario: Launch and save a granularity analysis, empty label
  # Accès à l'interface de configuration d'une analyse
    Given I am on "orga/granularity/report/idCell/1/idGranularity/1/idCube/1"
    When I click element "#indicatorRatio_indicator"
    And I select "Camembert" from "chartType"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
  # Enregistrement de l'analyse préconfigurée, libellé vide
    When I click "Enregistrer"
    Then I should see the popup "Enregistrer la configuration de l'analyse"
    When I click element "#saveReport .btn:contains('Enregistrer')"
    Then the field "saveLabelReport" should have error: "La configuration n'a pas pu être enregistrée, car le libellé saisi est vide."

  @javascript
  Scenario: Launch and save a granularity analysis, non empty label
  # Accès à l'interface de configuration d'une analyse
    Given I am on "orga/granularity/report/idCell/1/idGranularity/1/idCube/1"
    And I wait for the page to finish loading
    When I click element "#indicatorRatio_indicator"
    And I select "Camembert" from "chartType"
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
    Then I should see the "granularity1Report" datagrid
    And the "granularity1Report" datagrid should contain a row:
      | label |
      | Analyse préconfigurée test |
  # Accès à l'analyse préconfigurée enregistrée
    When I click "Cliquer pour accéder" in the row 1 of the "granularity1Report" datagrid
    Then I should see "Analyse préconfigurée test Niveau organisationnel global"
  # Vérification que l'analyse préconfigurée est bien présente dans la cellule globale
    When I click "Retour"
    And I open tab "Analyses"
    Then I should see the "report" datagrid
    And the "report" datagrid should contain a row:
      | label                      |
      | Analyse préconfigurée test |
  # Accès à l'analyse de la cellule
    When I click "Cliquer pour accéder" in the row 1 of the "report" datagrid
    Then I should see "Analyse préconfigurée test Vue globale"

  @javascript
  Scenario: Update a granularity analysis, without any change except on filters
    Given I am on "orga/granularity/report/idCell/1/idGranularity/1/idReport/2"
    And I wait for the page to finish loading
    Then I should see "Chiffre d'affaire, par année Niveau organisationnel global"
  # Ajout d'un filtre
    When I open collapse "Filtres"
    And I click element "#filterAxiso_anneeNumberMembers_one"
    And I select "2013" from "selectAxiso_anneeMemberFilter"
    # And I focus on element "#applyReportConfiguration"
    And I click "Lancer"
    Then the following message is shown and closed: "Analyse effectuée."
  # On utilise "click element" pour scroller vers le haut (focus)
    When I click element "#saveReportButton.btn:contains('Enregistrer')"
    Then I should see the popup "Enregistrer la configuration de l'analyse"
  # Par défaut, il est proposé de mettre à jour la configuration existante
    When I click element "#saveReport .btn:contains('Enregistrer')"
  # Vérification que le filtre sur 2013 est bien présent sur les analyses de la ou des cellule(s) correspondant à cette granularité
    And I click "Retour"
    And I open tab "Analyses"
    Then the row 2 of the "report" datagrid should contain:
      | label                        |
      | Chiffre d'affaire, par année |
    When I click "Cliquer pour accéder" in the row 2 of the "report" datagrid
    And I open tab "Valeurs"
    Then the "reportValues" datagrid should contain 1 row
    And the row 1 of the "reportValues" datagrid should contain:
      | valueAxiso_annee |
      | 2013                |


















