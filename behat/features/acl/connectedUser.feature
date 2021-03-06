@dbFull
Feature: Connected user feature

  @javascript @readOnly
  Scenario: Connected user scenario
  # TODO : Tester l'affichage et le clic sur les onglets du menu
    Given I am on the homepage
    And I wait for the page to finish loading
  # Login en tant qu'utilisateur connecté
    When I fill in "email" with "utilisateur.connecte@toto.com"
    And I fill in "password" with "utilisateur.connecte@toto.com"
    And I click "connection"
    Then I should see "Vous n'avez accès à aucun compte ou workspace"
  # Click sur "My C-Tool"
    When I click "My C-Tool"
    Then I should see "Vous n'avez accès à aucun compte ou workspace"
  # TODO…
#  # Accès à l'arbre des familles en consultation
#    When I am on "parameter/family/tree"
#    And I wait for the page to finish loading
#    And I wait 3 seconds
#    Then I should see "Catégorie contenant une sous-catégorie"
#  # Accès à la liste des familles en consultation
#    When I am on "parameter/family/list"
#    And I wait for the page to finish loading
#    Then I should see the "familyDatagrid" datagrid
#  # Accès à la page des unités standard
#    When I am on "unit/consult/standardunits"
#    And I wait for the page to finish loading
#    Then I should see the "ListStandardUnits" datagrid
#  # Accès à la page des unités étendues
#    When I am on "unit/consult/extendedunits"
#    And I wait for the page to finish loading
#    Then I should see the "ListExtendedUnit" datagrid
#  # Accès à la page des unités discrètes
#    When I am on "unit/consult/discreteunits"
#    And I wait for the page to finish loading
#    Then I should see the "ListDiscreteUnit" datagrid
#  # Accès à la page des grandeurs physiques
#    When I am on "unit/consult/physicalquantities"
#    And I wait for the page to finish loading
#    Then I should see the "ListPhysicalQuantity" datagrid



