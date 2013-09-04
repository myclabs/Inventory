@dbFull
Feature: Classification indicator feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Creation of a classification indicator, correct input
    Given I am on "classif/indicator/manage"
    Then I should see the "editIndicators" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un indicateur"
  # Ajout d'un indicateur, saisie correcte
    When I fill in "editIndicators_label_addForm" with "Test"
    And I fill in "editIndicators_ref_addForm" with "test"
    When I fill in "editIndicators_unit_addForm" with "t_co2e"
    And I fill in "editIndicators_ratioUnit_addForm" with "kg_co2e"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout effectué."
    And the row 5 of the "editIndicators" datagrid should contain:
      | label | ref  | unit       | ratioUnit   |
      | Test  | test | t équ. CO2 | kg équ. CO2 |

  @javascript
  Scenario: Creation of a classification indicator, incorrect input
    Given I am on "classif/indicator/manage"
    Then I should see the "editIndicators" datagrid
  # Popup d'ajout
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un indicateur"
  # Ajout, identifiant vide
    When I click "Valider"
    Then the field "editIndicators_ref_addForm" should have error: "Merci de renseigner ce champ."
    And the field "editIndicators_unit_addForm" should have error: "Merci de saisir un identifiant d'unité correct."
    And the field "editIndicators_ratioUnit_addForm" should have error: "Merci de saisir un identifiant d'unité correct."
  # Ajout, identifiant avec des caractères non autorisés
    When I fill in "editIndicators_ref_addForm" with "bépo"
    And I click "Valider"
    Then the field "editIndicators_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout, identifiant déjà utilisé
    When I fill in "editIndicators_label_addForm" with "Test"
    And I fill in "editIndicators_ref_addForm" with "ges"
    And I click "Valider"
    Then the field "editIndicators_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Ajout, unités non existantes
    When I fill in "editIndicators_unit_addForm" with "auie"
    And I fill in "editIndicators_ratioUnit_addForm" with "auie"
    And I click "Valider"
    Then the field "editIndicators_unit_addForm" should have error: "Merci de saisir un identifiant d'unité correct."
    And the field "editIndicators_ratioUnit_addForm" should have error: "Merci de saisir un identifiant d'unité correct."
  # Ajout, unités existantes mais non compatibles
    When I fill in "editIndicators_unit_addForm" with "t"
    And I fill in "editIndicators_ratioUnit_addForm" with "m"
    And I click "Valider"
    Then the field "editIndicators_unit_addForm" should have error: "Merci de saisir deux unités compatibles."
    And the field "editIndicators_ratioUnit_addForm" should have error: "Merci de saisir deux unités compatibles."
  # Ajout, test bouton "Annuler"
    When I click "Annuler"
    Then I should not see "Ajout d'un indicateur"

  @javascript
  Scenario: Edition of a classification indicator, correct input
  # TODO : position
    Given I am on "classif/indicator/manage"
    Then I should see the "editIndicators" datagrid
  # Modification des différents attributs, saisie correcte
    When I set "GES modifié" for column "label" of row 1 of the "editIndicators" datagrid with a confirmation message
    And I set "ges_modifie" for column "ref" of row 1 of the "editIndicators" datagrid with a confirmation message
    And I set "kg_co2e" for column "unit" of row 1 of the "editIndicators" datagrid with a confirmation message
    And I set "t_co2e" for column "ratioUnit" of row 1 of the "editIndicators" datagrid with a confirmation message
    Then the row 1 of the "editIndicators" datagrid should contain:
      | label       | ref         | unit        | ratioUnit  |
      | GES modifié | ges_modifie | kg équ. CO2 | t équ. CO2 |

  @javascript
  Scenario: Edition of a classification indicator, incorrect input
    Given I am on "classif/indicator/manage"
    Then I should see the "editIndicators" datagrid
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
    Then the following message is shown and closed: "Merci de saisir un identifiant d'unité correct."
  # Modification de l'unité, unité incorrecte
    When I set "auie" for column "unit" of row 1 of the "editIndicators" datagrid
    Then the following message is shown and closed: "Merci de saisir un identifiant d'unité correct."
  # Modification de l'unité, unité incompatible avec l'unité pour ratio
    When I set "t" for column "unit" of row 1 of the "editIndicators" datagrid
    Then the following message is shown and closed: "Merci de saisir deux unités compatibles."
  # Modification de l'unité pour ratio, unité vide
    When I set "" for column "ratioUnit" of row 1 of the "editIndicators" datagrid
    Then the following message is shown and closed: "Merci de saisir un identifiant d'unité correct."
  # Modification de l'unité pour ratio, unité incorrecte
    When I set "auie" for column "ratioUnit" of row 1 of the "editIndicators" datagrid
    Then the following message is shown and closed: "Merci de saisir un identifiant d'unité correct."
  # Modification de l'unité pour ratio, unité incompatible avec l'unité pour ratio
    When I set "m" for column "ratioUnit" of row 1 of the "editIndicators" datagrid
    Then the following message is shown and closed: "Merci de saisir deux unités compatibles."
  # Vérification que aucune des actions précédentes n'a entraîné de modification
    And the row 1 of the "editIndicators" datagrid should contain:
      | label | ref | unit       | ratioUnit   |
      | GES   | ges | t équ. CO2 | kg équ. CO2 |

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
