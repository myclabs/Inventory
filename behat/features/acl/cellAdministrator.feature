@dbFull
Feature: Cell administrator feature

  @javascript
  Scenario: Single cell administrator login scenario
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "administrateur.zone-marque@toto.com"
    And I fill in "password" with "administrateur.zone-marque@toto.com"
    And I click "connection"
  # On tombe sur la page de la cellule
    Then I should see "Europe | Marque A Organisation avec données"
    When I wait 5 seconds
    And I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity2Input8" datagrid
  # Vérification que le libellé "Vue globale" est présent mais non cliquable
  # Voir "Organization navigation scenario"
    And I should see "Vue globale"
    And I should not see a "#navigationParent a:contains('Vue globale')" element

  @javascript
  Scenario: Several cells administrator login scenario
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "administrateur.site@toto.com"
    And I fill in "password" with "administrateur.site@toto.com"
    And I click "connection"
  # On tombe sur le datagrid des cellules
    Then I should see the "listCells" datagrid
    And the "listCells" datagrid should contain 2 row
    And the row 1 of the "listCells" datagrid should contain:
      | label  | access         |
      | Annecy | Administrateur |
  # Accès à une des cellules
    When I click "Cliquer pour accéder" in the row 1 of the "listCells" datagrid
    Then I should see "Annecy Organisation avec données"
    When I open collapse "Année | Site | Catégorie"
    Then I should see the "aFGranularity5Input8" datagrid

  @javascript @skipped
  Scenario: Cell administrator organization tab
  #6272 Scenario: Cell administrator organization tab
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "administrateur.zone-marque@toto.com"
    And I fill in "password" with "administrateur.zone-marque@toto.com"
    And I click "connection"
    Then I should see "Europe | Marque A Organisation avec données"
  # Vérification qu'on a bien accès à l'onglet "Organisation" et à ses sous-onglets
    When I open tab "Organisation"
  # On tombe sur l'onglet "Membres"
    And I open collapse "Site"
    Then I should see the "listMemberssite" datagrid
  # Ajout d'un membre, saisie correcte (parent renseigné en partie)
    When I click "Ajouter"
    Then I should see the popup "Ajout d'un membre à l'axe « Site »"
    When I fill in "listMemberssite_label_addForm" with "AAA"
    And I fill in "listMemberssite_ref_addForm" with "aaa"
    And I fill in "listMemberssite_broaderpays_addForm" with "france#"
    And I click "Valider"
    Then the following message is shown and closed: "Ajout en cours. En fonction des données présentes l'opération peut être instantanée ou nécessiter du temps ainsi qu'un rechargement de la page."
  # Modification d'un membre
    When I set "Annecy modifiée" for column "label" of row 1 of the "listMemberssite" datagrid with a confirmation message
    And I set "annecy_modifie" for column "ref" of row 1 of the "listMemberssite" datagrid with a confirmation message
    Then the row 1 of the "listMemberssite" datagrid should contain:
      | label           | ref     |
      | Annecy modifiée | annecy_modifie |
  # Suppression d'un membre
    When I click "Supprimer" in the row 1 of the "listMemberssite" datagrid
    Then I should see the popup "Demande de confirmation"
    When I click "Confirmer"
    Then the following message is shown and closed: "Suppression effectuée."
  # Accès à l'onglet "Sous-unités"
    When I open tab "Sous-unités"
    And I open collapse "Site"
    Then I should see the "child_c2_g3" datagrid
  # Accès à l'onglet "Pertinence"
    When I open tab "Pertinence"
    And I open collapse "Site"
    Then I should see the "relevant_c2_g3" datagrid
  # Édition de la pertinence : rendre non pertinente une cellule pertinente
    When I set "Non pertinente" for column "relevant" of row 1 of the "relevant_c2_g3" datagrid with a confirmation message
    Then the row 1 of the "relevant_c2_g3" datagrid should contain:
      | site   | relevant       | allParentsRelevant |
      | Annecy | Non pertinente | Toutes pertinentes |
  # Édition de la pertinence : rendre pertinente une cellule non pertinente
    When I set "Pertinente" for column "relevant" of row 1 of the "relevant_c2_g3" datagrid with a confirmation message
    Then the row 1 of the "relevant_c2_g3" datagrid should contain:
      | site   | relevant       | allParentsRelevant |
      | Annecy | Pertinente | Toutes pertinentes |