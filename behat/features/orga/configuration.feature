@dbFull
Feature: General info of an organization feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Edit organization label
  # Accès à l'onglet "Informations générales"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Informations générales"
    Then I should see the "inputGranularities" datagrid
  # Modification du libellé
    When I fill in "Libellé" with "Organisation avec données modifiée"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Vérification modification prise en compte
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Vue globale Organisation avec données modifiée"

  @javascript
  Scenario: Edit organization inventory granularity
  # Accès à l'onglet "Informations générales"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Informations générales"
    Then I should see the "inputGranularities" datagrid
  # Modification du niveau organisationnel des collectes
    And I select "Année" from "Niveau organisationnel des collectes"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Vérification modification prise en compte
    When I open tab "Collectes"
    And I open collapse "Année"
    Then I should see the "inventories4" datagrid
    And the "inventories4" datagrid should contain 2 row
    And the row 1 of the "inventories4" datagrid should contain:
      | annee  | inventoryStatus |
      | 2012   | Non lancé       |
  # Modification du niveau organisationnel des collectes, choix d'un niveau organisationnel plus fin que certaines saisies
  # TODO…

  @javascript
  Scenario: Add input granularity, incorrect input
  # Accès à l'onglet "Informations générales"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Informations générales"
    Then I should see the "inputGranularities" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un niveau organisationnel de saisie, et du niveau organisationnel correspondant pour le choix des formulaires comptables"
  # Ajout, granularité de choix des formulaires vide
    When I select "Niveau organisationnel global" from "Saisie"
    And I click "Valider"
    Then the field "Choix des formulaires" should have error: "Merci de renseigner ce champ."
  # Ajout, granularité de saisie vide
    When I select "" from "Saisie"
    When I select "Niveau organisationnel global" from "Choix des formulaires"
    And I click "Valider"
    Then the field "Saisie" should have error: "Merci de renseigner ce champ."
  # Ajout, granularité de saisie non plus fine que ou égale à la granularité de choix des formulaires
    When I select "Site" from "Saisie"
    When I select "Année" from "Choix des formulaires"
    And I click "Valider"
    Then the field "Saisie" should have error: "Merci de sélectionner un niveau organisationnel de saisie plus fin que ou égal au niveau organisationnel de choix des formulaires."
  # Ajout, granularité de saisie existante
    When I select "Année | Site | Catégorie" from "Saisie"
    When I select "Année" from "Choix des formulaires"
    And I click "Valider"
    Then the field "Saisie" should have error: "Merci de sélectionner pour la saisie un niveau organisationnel différent, celui-là est déjà configuré pour accueillir des saisies."

  @javascript
  Scenario: Add input granularity, correct input
  # Accès à l'onglet "Informations générales"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Informations générales"
    Then I should see the "inputGranularities" datagrid
  # Ajout, saisie correcte (granularité de saisie non plus fine que ou égale à la granularité des collectes)
  # Remarque : granularités identiques (sans importance)
    When I click "Ajouter"
    And I select "Année" from "Saisie"
    And I select "Année" from "Choix des formulaires"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Vérification (au passage ordre / à l'ordre conventionnel sur les granularités)
    And the row 3 of the "inputGranularities" datagrid should contain:
      | inputGranularity | inputConfigGranularity |
      | Année            | Année                  |
  # Ajout, saisie correcte (granularité de saisie plus fine que ou égale à la granularité des collectes)
  # Remarque : granularités identiques (sans importance)
  # Remarque : test abandonné car pas de granularité plus fine encore disponible
  #  When I click "Ajouter"
  #  And I select "Année | Site" from "Saisie"
  #  And I select "Année | Site" from "Choix des formulaires"
  #  And I click "Valider"
  #  Then the following message is shown and closed: "Ajout effectué."
  # Vérification (au passage ordre / à l'ordre conventionnel sur les granularités)
    And the row 4 of the "inputGranularities" datagrid should contain:
      | inputGranularity | inputConfigGranularity |
      | Année \| Site    | Niveau organisationnel global |

  @javascript
  Scenario: Delete input granularity
  # Accès à l'onglet "Informations générales"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Informations générales"
    Then I should see the "inputGranularities" datagrid
  # Suppression d'une granularité de saisie avec des saisies
    When I click "Supprimer" in the row 1 of the "inputGranularities" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "inputGranularities" datagrid should contain 3 row


