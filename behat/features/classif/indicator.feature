@dbFull
Feature: Classification indicator feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a classification indicator
    Given I am on "classif/indicator/manage"
    Then I should see the "editIndicators" datagrid
  # Ajout d'un indicateur, identifiant vide
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un indicateur"
    When I click "Valider"
    Then the field "editIndicators_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout d'un indicateur, identifiant avec des caractères non autorisés
    When I fill in "editIndicators_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "editIndicators_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # TODO : tester la validité des unités, une fois la fonctionnalité implémentée
  # Ajout d'un indicateur, identifiant déjà utilisé
    When I fill in "editIndicators_label_addForm" with "Test"
    And I fill in "editIndicators_ref_addForm" with "ges"
    And I fill in "editIndicators_unit_addForm" with "t_co2e"
    And I fill in "editIndicators_ratioUnit_addForm" with "kg_co2e"
    And I click "Valider"
    Then the field "editIndicators_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Ajout d'un indicateur, saisie correcte
    When I fill in "editIndicators_ref_addForm" with "test"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 3 of the "editIndicators" datagrid should contain:
      | label       | ref         | unit    | ratioUnit |
      | Test | test | t_co2e  | kg_co2e   |
  # Ajout d'un indicateur, test bouton "Annuler"
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un indicateur"
    When I click "Annuler"
    Then I should not see "Ajout d'un indicateur"

  @javascript
  Scenario: Edition of a classification indicator
  # TODO : contraintes sur l'édition des unités
  # TODO : position
    Given I am on "classif/indicator/manage"
    Then I should see the "editIndicators" datagrid
  # Modification des différents attributs, saisie correcte
    When I set "GES modifié" for column "label" of row 1 of the "editIndicators" datagrid with a confirmation message
    And I set "ges_modifie" for column "ref" of row 1 of the "editIndicators" datagrid with a confirmation message
    And I set "mwh" for column "unit" of row 1 of the "editIndicators" datagrid with a confirmation message
    And I set "kwh" for column "ratioUnit" of row 1 of the "editIndicators" datagrid with a confirmation message
    Then the row 1 of the "editIndicators" datagrid should contain:
      | label       | ref         | unit | ratioUnit |
      | GES modifié | ges_modifie | mwh  | kwh       |
  # Modification de l'identifiant, identifiant vide
    When I set "" for column "ref" of row 1 of the "editIndicators" datagrid
    Then the following message is shown and closed: "Merci de renseigner ce champ."
  # Modification de l'identifiant, identifiant avec des caractères non autorisés
    When I set "bépo" for column "ref" of row 1 of the "editIndicators" datagrid
    Then the following message is shown and closed: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Modification de l'identifiant, identifiant déjà utilisé
    When I set "chiffre_affaire" for column "ref" of row 1 of the "editIndicators" datagrid
    Then the following message is shown and closed: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Modification de l'unité, unité vide
    When I set "" for column "unit" of row 1 of the "editIndicators" datagrid
    Then the following message is shown and closed: "Modification effectuée."
  # Modification de l'unité, unité incorrecte
    When I set "auie" for column "unit" of row 1 of the "editIndicators" datagrid
    Then the following message is shown and closed: "Modification effectuée."
  # Modification de l'unité, unité incompatible avec l'unité pour ratio
    When I set "t" for column "unit" of row 1 of the "editIndicators" datagrid
    Then the following message is shown and closed: "Modification effectuée."
  # Modification de l'unité pour ratio, unité vide
    When I set "" for column "ratioUnit" of row 1 of the "editIndicators" datagrid
    Then the following message is shown and closed: "Modification effectuée."
  # Modification de l'unité pour ratio, unité incorrecte
    When I set "auie" for column "ratioUnit" of row 1 of the "editIndicators" datagrid
    Then the following message is shown and closed: "Modification effectuée."
  # Modification de l'unité pour ratio, unité incompatible avec l'unité pour ratio
    When I set "m" for column "ratioUnit" of row 1 of the "editIndicators" datagrid
    Then the following message is shown and closed: "Modification effectuée."

  @javascript
  Scenario:  Deletion of a classification indicator
    Given I am on "classif/indicator/manage"
    Then I should see the "editIndicators" datagrid
    And the row 3 of the "editIndicators" datagrid should contain:
      | label                         | ref                           |
      | Sans indicateur contextualisé | sans_indicateur_contextualise |
  # Indicateur non utilisé par un indicateur contextualisé
    When I click "Supprimer" in the row 3 of the "editIndicators" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Indicateur utilisé par un indicateur contextualisé
    When I click "Supprimer" in the row 1 of the "editIndicators" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Cet indicateur ne peut pas être supprimé, car il est utilisé pour (au moins) un indicateur contextualisé."
