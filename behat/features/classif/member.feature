@dbFull
Feature: Classification member feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a classification member (axis without broader axis)
    When I am on "classif/member/manage"
  # Ouverture du volet "Gaz"
    And I open collapse "Gaz"
    Then I should see the "membersgaz" datagrid
    And the "membersgaz" datagrid should contain 2 row
  # Ajout d'un élément à l'axe "Gaz", identifiant vide
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un élément à l'axe « Gaz »"
    When I click "Valider"
    Then the field "membersgaz_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout d'un élément, identifiant avec caractères non autorisés
    When I fill in "membersgaz_ref_addForm" with "bépo"
    When I click "Valider"
    Then the field "membersgaz_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout d'un élément, saisie correcte
    When I fill in "membersgaz_label_addForm" with "AAA"
    And I fill in "membersgaz_ref_addForm" with "aaa"
    When I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 3 of the "membersgaz" datagrid should contain:
      | label | ref |
      | AAA   | aaa |
  # Ajout d'un élément, identifiant déjà utilisé
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un élément à l'axe « Gaz »"
    And I fill in "membersgaz_ref_addForm" with "co2"
    When I click "Valider"
    Then the field "membersgaz_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Bouton "Annuler"
    When I click "Annuler"
    Then I should not see "Ajout d'un élément à l'axe « Gaz »"

  @javascript
  Scenario: Creation of a classification member (axis with broader axis)
  # Ajout d'un élément, élément parent non renseigné
    When I am on "classif/member/manage"
    And I open collapse "Poste article 75"
    Then I should see the "membersposte_article_75" datagrid
    When I click "Ajouter"
    When I fill in "membersposte_article_75_label_addForm" with "Élément sans parent bis"
    And I fill in "membersposte_article_75_ref_addForm" with "element_sans_parent_bis"
    When I click "Valider"
    Then the field "membersposte_article_75_broaderscope_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout d'un élément, élément parent renseigné
    When I fill in "membersposte_article_75_label_addForm" with "Élément avec parent"
    And I fill in "membersposte_article_75_ref_addForm" with "element_avec_parent"
    And I select "1" from "membersposte_article_75_broaderscope_addForm"
    When I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 3 of the "membersposte_article_75" datagrid should contain:
      | label               | ref                 | broaderscope |
      | Élément avec parent | element_avec_parent | 1            |


  @javascript
  Scenario: Edition of a classification member (label and identifier)
    When I am on "classif/member/manage"
    And I open collapse "Gaz"
    Then I should see the "membersgaz" datagrid
  # Édition du libellé et de l'identifiant, saisie correcte
    When I set "CO2 modifié" for column "label" of row 1 of the "membersgaz" datagrid with a confirmation message
    And I set "co2_modifie" for column "ref" of row 1 of the "membersgaz" datagrid with a confirmation message
    Then the row 1 of the "membersgaz" datagrid should contain:
      | label | ref |
      | CO2 modifié   | co2_modifie |
  # Édition de l'identifiant, saisie vide
    When I set "" for column "ref" of row 1 of the "membersgaz" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Édition de l'identifiant, saisie avec des caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "membersgaz" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Édition de l'identifiant, identifiant déjà utilisé
    When I set "ch4" for column "ref" of row 1 of the "membersgaz" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: Edition of a classification member (position and parent)
  # TODO : Autoriser, dans les interfaces, la modification à "vide" d'un élément parent
  # TODO : Tester la modification du numéro d'ordre d'un élément
  # Modification élément parent
    When I am on "classif/member/manage"
    And I open collapse "Poste article 75"
    Then I should see the "membersposte_article_75" datagrid
    When I set "2" for column "broaderscope" of row 1 of the "membersposte_article_75" datagrid with a confirmation message
    Then the row 1 of the "membersposte_article_75" datagrid should contain:
      | broaderscope |
      | 2   |

  @javascript
  Scenario:  Deletion of a classification member
    When I am on "classif/member/manage"
    And I wait 5 seconds
  # Suppression d'un élément, sans obstacle
    And I open collapse "Scope"
    Then I should see the "membersscope" datagrid
    And the "membersscope" datagrid should contain 3 row
    When I click "Supprimer" in the row 3 of the "membersscope" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
    And the "membersscope" datagrid should contain 2 row
  # Tentative de suppression d'un élément possédant un élément enfant
    When I click "Supprimer" in the row 1 of the "membersscope" datagrid
    And I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # La tentative a abouti
    And the "membersscope" datagrid should contain 1 row