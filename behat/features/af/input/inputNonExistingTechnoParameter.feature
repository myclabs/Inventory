@dbFull @readOnly
Feature: Input with a non existing Parameter parameter feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Input with a non existing Parameter parameter scenario
  # Accès au formulaire
    Given I am on "af/af/test/id/1"
    And I wait for the page to finish loading
  # Saisie
    And I select "Gaz naturel" from "nature_combustible"
    And I fill in "quantite_combustible" with "10"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué. La saisie est complète, mais un problème est intervenu dans l'exécution des calculs : le paramètre « processus -> combustion, combustible -> gaz_naturel » de la famille « Combustion de combustible, mesuré en unité de masse » est introuvable."
  # Vérification du statut "orange" (saisie complète, calculs incomplets).
    And I should see "Saisie complète, calculs incomplets"
  # Contenu de l'onglet "Résultats"
    When I open tab "Résultats"
    Then I should see "Enregistrement effectué. La saisie est complète, mais un problème est intervenu dans l'exécution des calculs : le paramètre « processus -> combustion, combustible -> gaz_naturel » de la famille « Combustion de combustible, mesuré en unité de masse » est introuvable."
  # Contenu de l'onglet "Détails calculs"
    When I open tab "Détails calculs"
    Then I should see "La saisie enregistrée est complète mais un problème est intervenu au cours de l'exécution des calculs."
  # TODO : préciser le message à afficher (paramètre non trouvé).
