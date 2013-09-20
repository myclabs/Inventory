@dbFull
Feature: General data input feature

  Background:
    Given I am logged in

  @javascript
  Scenario: General data no input scenario
  # Formulaire des données générales : un seul champ "Chiffre d'affaires"
    Given I am on "af/af/test/id/2"
    And I wait for the page to finish loading
  # Contenu des onglets "Résultats" et "Détails calculs" en l'absence de saisie
    When I open tab "Résultats"
    Then I should see "La saisie enregistrée est incomplète, ses résultats ne peuvent être affichés."
    When I open tab "Détails calculs"
    Then I should see "La saisie enregistrée est incomplète, ses résultats ne peuvent être affichés."
    When I open tab "Saisie"
  # Aperçu des résultats, champ obligatoire non rempli (on ne peut pas cliquer sur "Enregistrer")
    And I click "Aperçu des résultats"
    Then the field "chiffre_affaire" should have error: "Merci de renseigner ce champ."
    And I should see "La saisie enregistrée est incomplète, ses résultats ne peuvent être affichés."
  # Bouton "Quitter", en l'absence de saisie
    When I click "Quitter"
    Then I should see the "listAF" datagrid

  @javascript
  Scenario: General data incorrect input scenario
    Given I am on "af/af/test/id/2"
    And I wait for the page to finish loading
  # Saisie nombre incorrect
    When I fill in "chiffre_affaire" with "auie"
    And I click "Aperçu des résultats"
    Then the field "chiffre_affaire" should have error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
    When I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie incomplète)."
    And the field "chiffre_affaire" should have error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
  # Saisie nombre séparateur décimal incorrect
    When I fill in "chiffre_affaire" with "1.5"
    And I click "Aperçu des résultats"
    Then the field "chiffre_affaire" should have error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
  # Saisie incertitude incorrecte (nombre vide)
    When I fill in "chiffre_affaire" with ""
    And I fill in "percentchiffre_affaire" with "auie"
    And I click "Aperçu des résultats"
    Then the field "chiffre_affaire" should have error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
  # Saisie incertitude séparateur décimal incorrect (nombre vide)
    When I fill in "percentchiffre_affaire" with "5.3"
    And I click "Aperçu des résultats"
    Then the field "chiffre_affaire" should have error: "La quantité saisie n'a pas pu être interprétée, merci de corriger (en français merci d'utiliser la virgule comme séparateur décimal)."
  # Bouton "Quitter" (colorié en rouge), popup de confirmation
    When I click "Quitter"
    Then I should see "Perte des modifications non enregistrées. Poursuivre ?"
    When I click "Annuler"
    And I click "Quitter"
    Then I should see "Perte des modifications non enregistrées. Poursuivre ?"
    When I click "Confirmer"
    Then I should see the "listAF" datagrid

  @javascript
  Scenario: General data correct input scenario
    Given I am on "af/af/test/id/2"
    And I wait for the page to finish loading
  # Bon affichage du séparateur de milliers en français
  # Affichage du bon nombre de chiffres significatifs
  # Incertitude non obligatoire
    When I fill in "chiffre_affaire" with "12345,6789"
    And I click "Aperçu des résultats"
    Then I should see "12 300"
  # Bon affichage du séparateur décimal en français
  # Bon arrondi au nombre de chiffres significatifs
    When I fill in "chiffre_affaire" with "0,1236"
    And I click "Aperçu des résultats"
    Then I should see "0,124"
  # Le clic sur "Aperçu des résultats" ne modifie pas le pourcentage d'avancement
    And I should see "0%"
  # Enregistrement, saisie complète
    When I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    And I should see "Saisie complète"
    And I should see "100%"
  # Onglet "Résultats"
    When I open tab "Résultats"
    Then I should see "Total : 0,124 k€"
  # Onglet "Détails calculs"
    When I open tab "Détails calculs"
    And I open collapse "Formulaire maître"
  # Vérification contenu pour un algo de type "Saisie de champ numérique"
    And I open collapse "chiffre_affaire"
    Then I should see "Type : Saisie"
    And I should see "Champ : Chiffre d'affaire"
    And I should see "Valeur : 0,1236 k€ ± %"
  # Modification saisie existante
    When I open tab "Saisie"
    When I fill in "chiffre_affaire" with ""
    Then I should see "Saisie en cours"
  # Bouton "Réinitialiser"
    And I click "Réinitialiser"
    And I click "Confirmer"
    Then I should see "Saisie complète"
  # Marquer la saisie comme terminée
    When I check "Marquer la saisie comme terminée"
    And I wait 5 seconds
    Then the following message is shown and closed: "Statut d'avancement modifié."
    And I should see "Saisie terminée"
  # Marquer la saisie comme non terminée
    When I uncheck "Marquer la saisie comme terminée"
    And I wait 5 seconds
    Then the following message is shown and closed: "Statut d'avancement modifié."
    And I should see "Saisie complète"
  # Bouton "Quitter" (colorié en gris), retour direct au datagrid des formulaires
    When I click "Quitter"
    Then I should see the "listAF" datagrid

  @javascript
  Scenario: General data change input unit scenario
    Given I am on "af/af/test/id/2"
    And I wait for the page to finish loading
  # Saisie valeur
    When I fill in "chiffre_affaire" with "1000"
  # Choix unité de saisie
    And I select "euro" from "chiffre_affaire_unit"
  # Aperçu des résultats
    And I click "Aperçu des résultats"
    Then I should see "Total : 1 k€"
  # Enregistrement et contenu de l'onglet "Résultats"
    When I click "Enregistrer"
    And I open tab "Résultats"
    Then I should see "Total : 1 k€"
  # Contenu de l'onglet "Détails calculs$
    When I open tab "Détails calculs"
    And I open collapse "Formulaire maître"
    And I open collapse "chiffre_affaire"
    Then I should see "Valeur : 1 000 € ± %"
  # Revenir sur la saisie et vérifier que l'unité choisie est toujours bien sélectionnée
    When I open tab "Saisie"
    And I click "Quitter"
    And I click "Test" in the row 2 of the "listAF" datagrid
    And I click "Aperçu des résultats"
    Then I should see "Total : 1 k€"


