@dbOneOrganizationWithAxes
Feature: orgaMember

  Background:
    Given I am logged in

  @javascript
  Scenario: orgaMember1
  # Accès à l'onglet "Membres"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Organisation"
    And I open tab "Membres"
    Then I should see "Année"
  # Déplier un volet
    When I open collapse "Année"
    Then I should see the "listMembersannee" datagrid
  # Ajout d'un membre, identifiant vide
    When I click element "#membersannee_wrapper a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un membre à l'axe « Année »"
    When I click element "#listMembersannee_addPanel button:contains('Valider')"
    Then the field "listMembersannee_ref_addForm" should have error: "Merci de renseigner ce champ."
  # Ajout d'un membre, identifiant avec caractères non autorisés
    When I fill in "listMembersannee_ref_addForm" with "bépo"
    And I click element "#listMembersannee_addPanel button:contains('Valider')"
    Then the field "listMembersannee_ref_addForm" should have error: "Merci d'utiliser seulement les caractères : \"a..z\", \"0..9\", et \"_\"."
  # Ajout d'un membre, saisie correcte
    When I fill in "listMembersannee_label_addForm" with "À supprimer"
    And I fill in "listMembersannee_ref_addForm" with "a_supprimer"
    And I click element "#listMembersannee_addPanel button:contains('Valider')"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
    And the row 1 of the "listMembersannee" datagrid should contain:
      | label            | ref |
      | À supprimer | a_supprimer |
  # Ajout d'un membre, identifiant déjà utilisé
    When I click element "#membersannee_wrapper a.btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un membre à l'axe « Année »"
    And I fill in "listMembersannee_ref_addForm" with "a_supprimer"
    And I click element "#listMembersannee_addPanel button:contains('Valider')"
    Then the field "listMembersannee_ref_addForm" should have error: "Merci de choisir un autre identifiant, celui-ci est déjà utilisé."
  # Suppression d'un membre, sans obstacle
    When I click element "#listMembersannee_addPanel a.btn:contains('Annuler')"
    And I click "Supprimer"
    Then I should see the popup "Demande de confirmation"
    When I click element "#listMembersannee_deletePanel a.btn:contains('Confirmer')"
    Then the following message is shown and closed: "Suppression effectuée."
    Then the "listMembersannee" datagrid should contain 0 row

  @javascript
  Scenario: orgaMember2
  # Accès à l'onglet "Membres"
    Given I am on "orga/cell/details/idCell/1"
    And I open tab "Organisation"
    And I open tab "Membres"
  # Ajout membre axe Pays, zone non renseignée
    When I open collapse "Pays"
    Then I should see the "listMemberspays" datagrid
    When I click element "#memberspays_wrapper .btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un membre à l'axe « Pays »"
    When I fill in "listMemberspays_label_addForm" with "France"
    And I fill in "listMemberspays_ref_addForm" with "france"
    And I click element "#listMemberspays_addPanel .btn:contains('Valider')"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
  # Ajout membre axe Site, pays renseigné
    When I open collapse "Site"
    Then I should see the "listMemberssite" datagrid
    When I click element "#memberssite_wrapper .btn:contains('Ajouter')"
    Then I should see the popup "Ajout d'un membre à l'axe « Site »"
    When I fill in "listMemberssite_label_addForm" with "Annecy"
    And I fill in "listMemberssite_ref_addForm" with "annecy"
    And I fill in "listMemberssite_broaderpays_addForm" with "france#"
    And I click element "#listMemberssite_addPanel .btn:contains('Valider')"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps. Dans ce dernier cas le résultat sera visible après rechargement de la page."
    And the row 1 of the "listMemberssite" datagrid should contain:
      | label  | ref      | broaderpays    |
      | Annecy | annecy   | France         |
  # Modification du membre parent suivant l'axe "Pays" (modification de "France" à "vide")
    When I set "" for column "broaderpays" of row 1 of the "listMemberssite" datagrid with a confirmation message
    Then the row 1 of the "listMemberssite" datagrid should contain:
      | label  | ref      | broaderpays    |
      | Annecy | annecy   |          |
  # Modification du membre parent suivant l'axe "Pays" (modification de "vide" à "France")
    When I set "France" for column "broaderpays" of row 1 of the "listMemberssite" datagrid with a confirmation message
    Then the row 1 of the "listMemberssite" datagrid should contain:
      | label  | ref      | broaderpays    |
      | Annecy | annecy   | France         |
  # Tentative de suppression du pays "France"
    When I click "Supprimer" in the row 1 of the "listMemberspays" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click element "#listMemberspays_deletePanel .btn:contains('Confirmer')"
    # And I wait 10 seconds
    # Then the following message is shown and closed: "Ce membre ne peut pas être supprimé, car il joue le rôle de parent direct pour au moins un autre membre."


