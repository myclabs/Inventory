@dbWithClassifAxesIndicatorsContexts
Feature: classifMember

  Background:
    Given I am logged in

  @javascript
  Scenario: classifMember1
  # Ajout d'un membre, suppression sans obstacle
    When I am on "classif/member/manage"
  # Ouverture du volet "Gaz"
    And I open collapse "Gaz"
    Then I should see the "membersgaz" datagrid
    And the "membersgaz" datagrid should contain 0 row
  # Ajout d'un membre à l'axe "Gaz", identifiant vide
    When I click element "#gaz_wrapper a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un membre à l'axe « Gaz »"
    When I click element "#membersgaz_addPanel button:contains('Valider')"
    Then the field "membersgaz_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout d'un membre, identifiant avec caractères non autorisés
    When I fill in "membersgaz_ref_addForm" with "bépo"
    And I click element "#membersgaz_addPanel button:contains('Valider')"
    Then the field "membersgaz_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout d'un membre, saisie correcte
    When I fill in "membersgaz_label_addForm" with "CO2"
    And I fill in "membersgaz_ref_addForm" with "co2"
    And I click element "#membersgaz_addPanel button:contains('Valider')"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 1 of the "membersgaz" datagrid should contain:
      | label | ref |
      | CO2   | co2 |
  # Ajout d'un membre, identifiant déjà utilisé
    When I click element "#gaz_wrapper a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un membre à l'axe « Gaz »"
    And I fill in "membersgaz_ref_addForm" with "co2"
    And I click element "#membersgaz_addPanel button:contains('Valider')"
    Then the field "membersgaz_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Suppression d'un membre, sans obstacle
    When I click element "#membersgaz_addPanel a.btn:contains('Annuler')"
    And I click "Supprimer" in the row 1 of the "membersgaz" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click element "#membersgaz_deletePanel a.btn:contains('Confirmer')"
    Then the following message is shown and closed: "Suppression effectuée."
    Then the "membersgaz" datagrid should contain 0 row

  @javascript
  Scenario: classifMember2
  # Édition du libellé et de l'identifiant d'un membre
  # On commence par ajouter le membre
    When I am on "classif/member/manage"
    And I open collapse "Gaz"
    Then I should see the "membersgaz" datagrid
    When I click element "#gaz_wrapper a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un membre à l'axe « Gaz »"
    When I fill in "membersgaz_label_addForm" with "CO2"
    And I fill in "membersgaz_ref_addForm" with "co2"
    And I click element "#membersgaz_addPanel button:contains('Valider')"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 1 of the "membersgaz" datagrid should contain:
      | label | ref |
      | CO2   | co2 |
  # Édition du libellé
    When I set "CH4" for column "label" of row 1 of the "membersgaz" datagrid with a confirmation message
    Then the row 1 of the "membersgaz" datagrid should contain:
      | label | ref |
      | CH4   | co2 |
  # Édition de l'identifiant, saisie correcte
    When I set "ch4" for column "ref" of row 1 of the "membersgaz" datagrid with a confirmation message
    Then the row 1 of the "membersgaz" datagrid should contain:
      | label | ref |
      | CH4   | ch4 |
  # Édition de l'identifiant, saisie vide
    When I set "" for column "ref" of row 1 of the "membersgaz" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Édition de l'identifiant, saisie avec des caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "membersgaz" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout d'un autre membre pour tester modif identifiant déjà utilisé
    When I click element "#gaz_wrapper a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un membre à l'axe « Gaz »"
    When I fill in "membersgaz_label_addForm" with "N2O"
    And I fill in "membersgaz_ref_addForm" with "n2o"
    And I click element "#membersgaz_addPanel button:contains('Valider')"
    Then the following message is shown and closed: "Ajout effectué."
  # Édition de l'identifiant, identifiant déjà utilisé
    When I set "n2o" for column "ref" of row 1 of the "membersgaz" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."

  @javascript
  Scenario: classifMember3
  # Donnée créée : membre "Scope renseigné" ajouté à l'axe "Poste article 75"
    Given I am on "classif/member/manage"
  # Ajout d'un membre à l'axe "Scope"
    When I open collapse "Scope"
    Then I should see the "membersscope" datagrid
    When I click element "#scope_wrapper a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un membre à l'axe « Scope »"
    When I fill in "membersscope_label_addForm" with "1"
    And I fill in "membersscope_ref_addForm" with "1"
    And I click element "#membersscope_addPanel button:contains('Valider')"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 1 of the "membersscope" datagrid should contain:
      | label            | ref |
      | 1 | 1 |
  # Tentative de suppression d'un axe contenant un membre
    When I am on "classif/axis/manage"
    And I wait 5 seconds
    And I click "Scope"
    Then I should see the popup "Édition d'un axe"
    When I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click element "#editAxis_deletePanel button:contains('Confirmer')"
    Then the following message is shown and closed: "Pour pouvoir supprimer cet axe, merci de supprimer auparavant ses membres."
  # Ouverture du volet "Poste article 75"
    When I am on "classif/member/manage"
    And I open collapse "Poste article 75"
    Then I should see the "membersposte_article_75" datagrid
    And the "membersposte_article_75" datagrid should contain 0 row
  # Ajout d'un membre, membre parent renseigné
    When I click element "#poste_article_75_wrapper a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un membre à l'axe « Poste article 75 »"
    When I fill in "membersposte_article_75_label_addForm" with "Source fixe"
    And I fill in "membersposte_article_75_ref_addForm" with "source_fixe"
    And I select "1" from "membersposte_article_75_broaderscope_addForm"
    And I click element "#membersposte_article_75_addPanel button:contains('Valider')"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 1 of the "membersposte_article_75" datagrid should contain:
      | label            | ref |  broaderscope |
      | Source fixe | source_fixe | 1  |
  # Tentative d'ajout d'un membre, membre parent non renseigné
    When I click element "#poste_article_75_wrapper a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un membre à l'axe « Poste article 75 »"
    When I fill in "membersposte_article_75_label_addForm" with "Scope non renseigné"
    And I fill in "membersposte_article_75_ref_addForm" with "scope_non_renseigne"
    And I click element "#membersposte_article_75_addPanel button:contains('Valider')"
    Then the field "membersposte_article_75_broaderscope_addForm" should have error: "Merci de renseigner ce champ."
  # Tentative de suppression d'un membre possédant un membre enfant
    When I click element "#membersposte_article_75_addPanel a.btn:contains('Annuler')"
    And I open collapse "Scope"
    Then I should see the "membersscope" datagrid
    And I click "Supprimer" in the row 1 of the "membersscope" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click element "#membersscope_deletePanel a.btn:contains('Confirmer')"
    Then the following message is shown and closed: "Suppression effectuée."
  # La tentative a abouti
    And the row 1 of the "membersposte_article_75" datagrid should contain:
      | label            | ref |  broaderscope |
      | Source fixe | source_fixe |   |