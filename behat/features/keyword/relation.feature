@dbFull
Feature: Keywords relations

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a keyword relation, correct input
    Given I am on "keyword/association/manage"
    Then I should see the "association" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'une relation entre mots clés"
  # Ajout, saisie valide
    And I select "processus" from "association_subject_addForm"
    And I select "est plus général que" from "association_predicate_addForm"
    And I select "amont de la combustion" from "association_object_addForm"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
  # Vérification ligne ajoutée bien présente dans le datagrid
    And the "association" datagrid should contain a row:
      | subject   | predicate            | object                 |
      | processus | est plus général que | amont de la combustion |

  @javascript
  Scenario: Creation of a keyword relation, incorrect input
  # refs #6419 Positionnement d'un message d'erreur dans le popup d'ajout d'une relation entre mots clés
    Given I am on "keyword/association/manage"
    Then I should see the "association" datagrid
  # Popup d'ajout
    When I click "Ajouter"
  # Ajout, champs non renseignés
    When I click "Valider"
    Then the field "association_subject_addForm" should have error: "Merci de renseigner ce champ."
    And the field "association_predicate_addForm" should have error: "Merci de renseigner ce champ."
    And the field "association_object_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout, relation déjà existante
    When I select "combustible" from "association_subject_addForm"
    And I select "est plus général que" from "association_predicate_addForm"
    And I select "gaz naturel" from "association_object_addForm"
    And I click "Valider"
    Then the field "association_subject_addForm" should have error: "Les deux mots clés indiqués sont déjà reliés par le même prédicat."
    And the field "association_predicate_addForm" should have error: "Les deux mots clés indiqués sont déjà reliés par le même prédicat."
    And the field "association_object_addForm" should have error: "Les deux mots clés indiqués sont déjà reliés par le même prédicat."
  # Ajout, mots clés sujet et objet identigues
    When I select "combustible" from "association_object_addForm"
    And I click "Valider"
    Then the field "association_subject_addForm" should have error: "Merci de saisir des mots clés sujet et objet qui ne soient pas identiques."
    And the field "association_predicate_addForm" should have error: "Merci de saisir des mots clés sujet et objet qui ne soient pas identiques."
    And the field "association_object_addForm" should have error: "Merci de saisir des mots clés sujet et objet qui ne soient pas identiques."

  @javascript
  Scenario: Edition of a keyword relation
    Given I am on "http://localhost/inventory/keyword/association/manage"
    Then I should see the "association" datagrid
  # Modification du prédicat d'une relation
    And the row 1 of the "association" datagrid should contain:
      | predicate |
      | est plus général que  |
    When I set "contient" for column "predicate" of row 1 of the "association" datagrid with a confirmation message
    Then the row 1 of the "association" datagrid should contain:
      | predicate |
      | contient  |

  @javascript
  Scenario: Deletion of a keyword relation
    Given I am on "http://localhost/inventory/keyword/association/manage"
    Then I should see the "association" datagrid
  # Suppression d'une relation
    When I click "Supprimer" in the row 1 of the "association" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."



