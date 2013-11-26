@dbFull
Feature: Organizational member feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of an organizational member, correct input
  # Accès à l'onglet "Éléments"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Paramétrage"
    And I open tab "Éléments"
  # Accès au datagrid des sites
    And I open collapse "Site"
    Then I should see the "listMemberssite" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un élément à l'axe « Site »"
  # Ajout d'un élément, saisie correcte (parent renseigné en partie)
    When I fill in "listMemberssite_label_addForm" with "AAA"
    And I fill in "listMemberssite_ref_addForm" with "aaa"
    And I fill in "listMemberssite_broaderpays_addForm" with "france#da39a3ee5e6b4b0d3255bfef95601890afd80709"
    And I fill in "listMemberssite_broadermarque_addForm" with "marque_a#da39a3ee5e6b4b0d3255bfef95601890afd80709"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Affichage suivant l'ordre alphabétique des identifiants
    And the row 1 of the "listMemberssite" datagrid should contain:
      | label  | ref | broaderpays |
      | AAA    | aaa | France      |

  @javascript
  Scenario: Creation of an organizational member, incorrect input
  # Accès à l'onglet "Éléments"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Paramétrage"
    And I open tab "Éléments"
    Then I should see "Site"
  # Déplier un volet
    When I open collapse "Site"
    Then I should see the "listMemberssite" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un élément à l'axe « Site »"
  # Ajout, identifiant vide
    When I click "Valider"
    Then the field "listMemberssite_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant avec caractères non autorisés
    When I fill in "listMemberssite_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "listMemberssite_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé, éléments axes parents manquants
    When I fill in "listMemberssite_ref_addForm" with "annecy"
    And I click "Valider"
    Then the field "listMemberssite_broaderpays_addForm" should have error: "Merci de renseigner ce champ."
    And the field "listMemberssite_broadermarque_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, identifiant déjà utilisé, éléments axes parents remplis
    When I fill in "listMemberssite_broaderpays_addForm" with "france#da39a3ee5e6b4b0d3255bfef95601890afd80709"
    And I fill in "listMemberssite_broadermarque_addForm" with "marque_a#da39a3ee5e6b4b0d3255bfef95601890afd80709"
    And I click "Valider"
    Then the field "listMemberssite_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of an organizational member's attributes (label and identifier), correct input
  # Accès à l'onglet "Éléments"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Paramétrage"
    And I open tab "Éléments"
  # Ajout élément axe Pays, zone non renseignée
    When I open collapse "Site"
    Then I should see the "listMemberssite" datagrid
  # Modification du libellé et de l'identifiant d'un élément, saisie correcte
    When I set "Annecy modifiée" for column "label" of row 1 of the "listMemberssite" datagrid with a confirmation message
    And I set "annecy_modifie" for column "ref" of row 1 of the "listMemberssite" datagrid with a confirmation message
    Then the row 1 of the "listMemberssite" datagrid should contain:
      | label           | ref            |
      | Annecy modifiée | annecy_modifie |

  @javascript
  Scenario: Edition of an organizational member's attributes (label and identifier), incorrect input
  # Accès à l'onglet "Éléments"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Paramétrage"
    And I open tab "Éléments"
  # Ajout élément axe Pays, zone non renseignée
    When I open collapse "Site"
    Then I should see the "listMemberssite" datagrid
  # Modification de l'identifiant d'un élément, identifiant vide
    When I set "" for column "ref" of row 1 of the "listMemberssite" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant d'un élément, identifiant avec caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "listMemberssite" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant d'un élément, identifiant déjà utilisé
    When I set "chambery" for column "ref" of row 1 of the "listMemberssite" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of parent member of an organizational member
  # Accès à l'onglet "Éléments"
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
    And I open tab "Paramétrage"
    And I open tab "Éléments"
    When I open collapse "Site"
    Then the row 1 of the "listMemberssite" datagrid should contain:
      | label  | ref      | broaderpays |
      | Annecy | annecy   | France      |
  # Modification de l'élément parent suivant l'axe "Marque"
    When I set "marque_b#da39a3ee5e6b4b0d3255bfef95601890afd80709" for column "broadermarque" of row 1 of the "listMemberssite" datagrid with a confirmation message
    # When I set "marque_b" for column "broadermarque" of row 1 of the "listMemberssite" datagrid with a confirmation message
    # When I set "Marque B" for column "broadermarque" of row 1 of the "listMemberssite" datagrid with a confirmation message
    And I wait 15 seconds
    Then the "listMemberssite" datagrid should contain a row:
      | label  | ref      | broaderpays    | broadermarque |
      | Annecy | annecy   | France         | Marque B      |
  # Modification de l'élément parent suivant l'axe "Pays" (tentative de modification de "France" à "vide", non autorisé)
    When I set "" for column "broaderpays" of row 1 of the "listMemberssite" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
    And the "listMemberssite" datagrid should contain a row:
      | label  | ref      | broaderpays    | broadermarque |
      | Annecy | annecy   | France         | Marque B      |

  @javascript
  Scenario: Check list of members of an axis when the current cell is not the global cell
    Given I am on "orga/cell/details/idCell/1"
    And I wait for the page to finish loading
  # Descendre dans la cellule "Europe | Marque B"
    When I click element ".icon-plus"
    And I select "Europe" from "zone"
    And I select "Marque B" from "marque"
    And I click element "#goTo2"
  # Vérification du contenu du datagrid des éléments de l'axe "Site"
    And I open tab "Paramétrage"
    And I open tab "Éléments"
    And I open collapse "Site"
    Then I should see the "listMemberssite" datagrid
    And the row 1 of the "listMemberssite" datagrid should contain:
      | label | ref |
      | Grenoble | grenoble |