@dbFull
Feature: General info of an organization feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Edit organization label
  # Accès à l'onglet "Informations générales"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Informations générales"
    Then I should see the "datagridUsers1" datagrid
  # Modification du libellé
    When I fill in "Libellé" with "Workspace avec données modifiée"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Vérification modification prise en compte
    When I reload the page
    And I wait for the page to finish loading
    Then I should see "Workspace avec données modifiée"

  @javascript
  Scenario: Edit organization inventory granularity
  # Accès à l'onglet "Informations générales"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Informations générales"
    Then I should see the "datagridUsers1" datagrid
  # Modification du niveau organisationnel des collectes
    And I select "Année" from "Niveau organisationnel des collectes"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Vérification modification prise en compte
    And I click "Données"
    And I wait for the page to finish loading
    Then I should see "Collectes Année"
    Then I should see "Collectes Année | Catégorie"
    Then I should see "Collectes Année | Zone | Marque"
    Then I should see "Collectes & Saisies Année | Site"
  # Modification du niveau organisationnel des collectes, choix d'un niveau organisationnel plus fin que certaines saisies
  # TODO…

  @javascript
  Scenario: Add input granularity, incorrect input
  # Accès à l'onglet "Informations générales"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Formulaires"
    Then I should see the "addGranularity1_afs" form
  # Ajout, granularité de saisie non plus fine que ou égale à la granularité de choix des formulaires
    When I additionally select "Site" from "axes"
    And I additionally select "Année" from "inputConfigAxes"
    And I click "Ajouter"
    Then I should see "Merci de sélectionner un niveau organisationnel de saisie plus fin que ou égal au niveau organisationnel de choix des formulaires."

  @javascript
  Scenario: Add input granularity, correct input
  # Accès à l'onglet "Informations générales"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Formulaires"
    Then I should see the "addGranularity1_afs" form
  # Ajout, saisie correcte (granularité de saisie non plus fine que ou égale à la granularité des collectes)
  # Remarque : granularités identiques (sans importance)
    When I additionally select "Année" from "axes"
    And I additionally select "Année" from "inputConfigAxes"
    And I click "Ajouter"
    Then the following message is shown and closed: "Ajout effectué."
    Then I should see "Année <small>Année</small>"
    And I open tab "Niveaux"
  # Vérification (au passage ordre / à l'ordre conventionnel sur les granularités)
    And the row 4 of the "orga_granularities" datagrid should contain:
      | axes  | afs  |
      | Année | Vrai |
  # Ajout, saisie correcte (granularité de saisie plus fine que ou égale à la granularité des collectes)
  # Remarque : granularités identiques (sans importance)
  # Remarque : test abandonné car pas de granularité plus fine encore disponible
  #  When I click "Ajouter"
  #  And I select "Année | Site" from "Saisie"
  #  And I select "Année | Site" from "Choix des formulaires"
  #  And I click "Valider"
  #  Then the following message is shown and closed: "Ajout effectué."
  # Vérification (au passage ordre / à l'ordre conventionnel sur les granularités)
#    And the row 4 of the "inputGranularities" datagrid should contain:
#      | inputGranularity | inputConfigGranularity |
#      | Année \| Site    | Niveau organisationnel global |

  @javascript
  Scenario: Delete input granularity
  # Accès à l'onglet "Informations générales"
    Given I am on "orga/organization/edit/idOrganization/1"
    And I wait for the page to finish loading
    And I open tab "Niveaux"
    Then I should see the "orga_granularities" datagrid
    And the row 1 of the "orga_granularities" datagrid should contain:
      | axes  | afs  |
      | Année | Vrai |
  # Suppression d'une granularité de saisie avec des saisies
    And I set "Non" for column "afs" of row 1 of the "orga_granularities" datagrid with a confirmation message
    And the row 1 of the "orga_granularities" datagrid should contain:
      | axes  | afs  |
      | Année | Faux |


