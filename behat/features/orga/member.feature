@dbFull
Feature: Organizational member feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an organizational member, correct input
  # Accès à l'onglet "Membres"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Membres"
  # Accès au datagrid des sites
    And I open collapse "Site"
    Then I should see the "listMemberssite" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un membre à l'axe « Site »"
  # Ajout d'un membre, saisie correcte (parent renseigné en partie)
    When I fill in "listMemberssite_label_addForm" with "AAA"
    And I fill in "listMemberssite_ref_addForm" with "aaa"
    And I fill in "listMemberssite_broaderpays_addForm" with "france#"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps ainsi qu'un rechargement de la page."
  # Affichage suivant l'ordre alphabétique des identifiants
    And the row 1 of the "listMemberssite" datagrid should contain:
      | label  | ref | broaderpays |
      | AAA    | aaa | France      |

  @javascript
  Scenario: Creation of an organizational member, incorrect input
  # Accès à l'onglet "Membres"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Membres"
    Then I should see "Site"
  # Déplier un volet
    When I open collapse "Site"
    Then I should see the "listMemberssite" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un membre à l'axe « Site »"
  # Ajout, identifiant vide
    When I click "Valider"
    Then the field "listMemberssite_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "listMemberssite_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "listMemberssite_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "listMemberssite_ref_addForm" with "annecy"
    And I click "Valider"
    Then the field "listMemberssite_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of an organizational member's attributes (label and identifier), correct input
  # Accès à l'onglet "Membres"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Membres"
  # Ajout membre axe Pays, zone non renseignée
    When I open collapse "Site"
    Then I should see the "listMemberssite" datagrid
  # Modification du libellé et de l'identifiant d'un membre, saisie correcte
    When I set "Annecy modifiée" for column "label" of row 1 of the "listMemberssite" datagrid with a confirmation message
    And I set "annecy_modifie" for column "ref" of row 1 of the "listMemberssite" datagrid with a confirmation message
    Then the row 1 of the "listMemberssite" datagrid should contain:
      | label           | ref     |
      | Annecy modifiée | annecy_modifie |

  @javascript
  Scenario: Edition of an organizational member's attributes (label and identifier), incorrect input
  # Accès à l'onglet "Membres"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Membres"
  # Ajout membre axe Pays, zone non renseignée
    When I open collapse "Site"
    Then I should see the "listMemberssite" datagrid
  # Modification de l'identifiant d'un membre, identifiant vide
    When I set "" for column "ref" of row 1 of the "listMemberssite" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant d'un membre, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "listMemberssite" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant d'un membre, identifiant déjà utilisé
    When I set "chambery" for column "ref" of row 1 of the "listMemberssite" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of parent member of an organizational member
  # Accès à l'onglet "Membres"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Membres"
    When I open collapse "Site"
    Then the row 1 of the "listMemberssite" datagrid should contain:
      | label  | ref      | broaderpays    |
      | Annecy | annecy   | France         |
  # Modification du membre parent suivant l'axe "Pays" (modification de "France" à "vide")
    When I set "" for column "broaderpays" of row 1 of the "listMemberssite" datagrid with a confirmation message
    Then the row 1 of the "listMemberssite" datagrid should contain:
      | label  | ref      | broaderpays    |
      | Annecy | annecy   |                |
  # Modification du membre parent suivant l'axe "Pays" (modification de "vide" à "France")
    When I set "france#" for column "broaderpays" of row 1 of the "listMemberssite" datagrid with a confirmation message
    And I wait 15 seconds
    Then the row 1 of the "listMemberssite" datagrid should contain:
      | label  | ref      | broaderpays    |
      | Annecy | annecy   | France         |

  @javascript
  Scenario: Deletion of an organizational member
    @skipped
  # Accès à l'onglet "Membres"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Organisation"
    And I open tab "Membres"
  # Membre jouant le rôle de parent direct pour au moins un autre membre
    And I open collapse "Pays"
    When I click "Supprimer" in the row 1 of the "listMemberspays" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Ce membre ne peut pas être supprimé, car il joue le rôle de parent direct pour au moins un autre membre."
  # Suppression d'un membre, sans obstacle
    When I open collapse "Année"
    And I click "Supprimer" in the row 1 of the "listMembersannee" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    Then the "listMembersannee" datagrid should contain 1 row

  @javascript
  Scenario: Check list of members of an axis when the current cell is not the global cell
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
  # Descendre dans la cellule "Europe | Marque B"
    When I select "Europe" from "zone"
    And I select "Marque B" from "marque"
    And I click element "#goTo2"
  # Vérification du contenu du datagrid des membres de l'axe "Site"
    And I open tab "Organisation"
    And I open tab "Membres"
    And I open collapse "Site"
    Then I should see the "listMemberssite" datagrid
    And the row 1 of the "listMemberssite" datagrid should contain:
      | label | ref |
      | Grenoble | grenoble |