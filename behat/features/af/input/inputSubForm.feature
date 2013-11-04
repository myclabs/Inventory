@dbFull
Feature: Subforms input feature

  Background:
    Given I am logged in

  @javascript
  Scenario: Subform input scenario : complete input with zero repetition
  # Formulaire des données générales : un seul champ "Chiffre d'affaires"
    Given I am on "af/af/test/id/3"
    And I wait for the page to finish loading
  # Saisie
    And I fill in "s_f_n_r__chiffre_affaire" with "10"
  # On commence par ajouter une répétition, juste pour tester l'affichage des messages d'erreur et le taux de complétudi
    And I click "Ajouter"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué, saisie incomplète. Vous pouvez renseigner les zones obligatoires manquantes maintenant ou plus tard."
    And the field "s_f_r__nature_combustible__1" should have error: "Merci de renseigner ce champ."
    And the field "s_f_r__quantite_combustible__1" should have error: "Merci de renseigner ce champ."
    And I should see "33%"
  # Puis on supprime le bloc pour enregistrer une saisie complète
    When I click "Supprimer"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    And I should see "100%"

  @javascript
  Scenario: Subform input scenario : complete input with 1 repetition
  # Formulaire des données générales : un seul champ "Chiffre d'affaires"
    Given I am on "af/af/test/id/3"
    And I wait for the page to finish loading
  # Saisie complète avec 1 répétition
    And I fill in "s_f_n_r__chiffre_affaire" with "10"
    And I click "Ajouter"
    And I select "Charbon" from "s_f_r__nature_combustible__1"
    And I fill in "s_f_r__quantite_combustible__1" with "10"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    And I should see "100%"

  @javascript
  Scenario: Subform input scenario : complete input with 2 repetitions
  # Formulaire des données générales : un seul champ "Chiffre d'affaires"
    Given I am on "af/af/test/id/3"
    And I wait for the page to finish loading
  # Saisie complète avec 2 répétitions
    And I fill in "s_f_n_r__chiffre_affaire" with "10"
    And I click "Ajouter"
    And I click "Ajouter"
    And I select "Charbon" from "s_f_r__nature_combustible__1"
    And I fill in "s_f_r__quantite_combustible__1" with "10"
    And I select "Charbon" from "s_f_r__nature_combustible__2"
    And I fill in "s_f_r__quantite_combustible__2" with "20"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Enregistrement effectué (saisie complète)."
    And I should see "100%"

  @javascript
  Scenario: Subform input scenario : display of free label in the result tab
    Given I am on "af/edit/menu/id/3"
    And I wait for the page to finish loading
    And I open tab "Composants"
    And I open collapse "Sous-formulaires répétés"
    And I set "Oui" for column "hasFreeLabel" of row 1 of the "subAfRepeatedDatagrid" datagrid with a confirmation message
    And I click "Test"
    And I click "Ajouter"
    And I fill in "s_f_r__freeLabel__1" with "Blablablabla"
    And I select "Charbon" from "s_f_r__nature_combustible__1"
    And I fill in "s_f_r__quantite_combustible__1" with "10"
    And I fill in "s_f_n_r__chiffre_affaire" with "10"
  # Vérification que le libellé libre saisi "apparaît" dans l'onglet "Résultats"
    And I click "Enregistrer"
    And I open tab "Résultats"
    Then I should see "Blablablabla"
  # Vérification que le libellé libre saisi "apparaît" dans l'onglet "Détails calculs"
    When I open tab "Détails calculs"
    Then I should see "Blablablabla"

  @javascript
  Scenario: Subform input scenario : choice of units
    Given I am on "af/af/test/id/3"
    And I wait for the page to finish loading
  # Saisie chiffre d'affaires avec unité modifiée
    And I select "euro" from "s_f_n_r__chiffre_affaire_unit"
    And I fill in "s_f_n_r__chiffre_affaire" with "1000"
  # Saisie sous-formulaire répété avec unité modifiée
    And I click "Ajouter"
    And I select "Charbon" from "s_f_r__nature_combustible__1"
    And I fill in "s_f_r__quantite_combustible__1" with "1000"
    And I select "kg" from "s_f_r__quantite_combustible_unit__1"
    And I click "Enregistrer"
    And I open tab "Détails calculs"
    And I open collapse "Sous-formulaire non répété"
    And I open collapse "chiffre_affaire"
  # Valeur récupérée dans la bonne unité
    Then I should see "Valeur : 1 000 € ± %"
    When I open collapse "Sous-formulaire répété #1"
    And I open collapse "emissions_combustion"
  # Calcul effectué avec la valeur récupérée dans la bonne unité
    Then I should see "Valeur : 3,077 t équ. CO2 ± 20 %"
  # Valeur récupérée dans la bonne unité
    When I open collapse "quantite_combustible"
    Then I should see "Valeur : 1 000 kg ± %"

