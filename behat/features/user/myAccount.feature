@dbFull
Feature: My account feature

  @javascript
  Scenario: Change my first and last name
    Given I am on the homepage
    And I wait for the page to finish loading
    When I fill in "email" with "emmanuel.risler.pro@gmail.com"
    And I fill in "password" with "emmanuel.risler.pro@gmail.com"
    And I click "connection"
    And I click "Mon compte"
  # Clic sur "Enregistrer" sans avoir rien changé
    When I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Saisie prénom et nom
    When I fill in "Prénom" with "Emmanuel"
    And I fill in "Nom" with "Risler"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."

  @javascript
  Scenario: Change my email address
    Given I am on the homepage
    And I wait for the page to finish loading
    When I fill in "email" with "emmanuel.risler.pro@gmail.com"
    And I fill in "password" with "emmanuel.risler.pro@gmail.com"
    And I click "connection"
    And I click "Mon compte"
  # Accès au formulaire de modification de l'adresse e-mail
    When I click element "#inputEmail + span .btn:contains('Éditer')"
    Then I should see "Mon compte — modification de l'adresse e-mail"
  # Les trois champs sont obligatoires, en HTML 5.
    When I click "Enregistrer"
  # Saisie avec un mauvais mot de passe, première adresse email existe déjà, seconde adresse email différente de la première
    When I fill in "Merci de re-saisir votre mot de passe" with "mauvais_mot_de_passe"
    And I fill in "Nouvelle adresse e-mail" with "emmanuel.risler.pro@gmail.com"
    And I fill in "Nouvelle adresse e-mail (confirmation)" with "adresse email différente"
    And I click "Enregistrer"
    Then the field "Merci de re-saisir votre mot de passe" should have error: "Le mot de passe indiqué est invalide."
    And the field "Nouvelle adresse e-mail" should have error: "Il existe déjà un compte utilisateur associé à cette adresse e-mail."
    And the field "Nouvelle adresse e-mail (confirmation)" should have error: "Cette saisie de la nouvelle adresse e-mail n'est pas identique à la précédente."
  # Bon mot de passe
    When I fill in "Merci de re-saisir votre mot de passe" with "emmanuel.risler.pro@gmail.com"
    And I click "Enregistrer"
    Then the field "Nouvelle adresse e-mail" should have error: "Il existe déjà un compte utilisateur associé à cette adresse e-mail."
    And the field "Nouvelle adresse e-mail (confirmation)" should have error: "Cette saisie de la nouvelle adresse e-mail n'est pas identique à la précédente."
  # Première adresse email non déjà existante
    When I fill in "Nouvelle adresse e-mail" with "emmanuel.risler.abo@gmail.com"
    And I click "Enregistrer"
    Then the field "Nouvelle adresse e-mail (confirmation)" should have error: "Cette saisie de la nouvelle adresse e-mail n'est pas identique à la précédente."
  # Seconde adresse email identique à la première
    And I fill in "Nouvelle adresse e-mail (confirmation)" with "emmanuel.risler.abo@gmail.com"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
    And the ".page-header h1" element should contain "Mon compte"
  # Déconnexion
    When I click "currentUserButton"
    And I click "Déconnexion"
    Then the following message is shown and closed: "Vous n'êtes pas connecté."
  # Reconnexion avec le nouvel email
    When I fill in "email" with "emmanuel.risler.abo@gmail.com"
    And I fill in "password" with "emmanuel.risler.pro@gmail.com"
    And I click "connection"
    Then I should see "Vous ne disposez d'aucun droit d'accès à une unité organisationnelle."

  @javascript
  Scenario: Change my password
    Given I am on the homepage
    And I wait for the page to finish loading
    When I fill in "email" with "emmanuel.risler.pro@gmail.com"
    And I fill in "password" with "emmanuel.risler.pro@gmail.com"
    And I click "connection"
    And I click "Mon compte"
  # Accès au formulaire de modification du mot de passe
    When I click element "#inputPassword + span .btn:contains('Éditer')"
    Then the ".page-header h1" element should contain "Mon compte — modification du mot de passe"
  # Les trois champs sont obligatoires, en HTML 5.
    When I click "Enregistrer"
  # Saisie avec ancien mot de passe invalide et second mot de passe différent du premier
    When I fill in "Ancien mot de passe" with "mauvais_mot_de_passe"
    And I fill in "Nouveau mot de passe" with "aaaaaaaa"
    And I fill in "Nouveau mot de passe (confirmation)" with "bbbbbbbb"
    And I click "Enregistrer"
    Then the field "Ancien mot de passe" should have error: "Le mot de passe indiqué est invalide."
    And the field "Nouveau mot de passe (confirmation)" should have error: "Cette saisie du nouveau mot de passe n'est pas identique à la précédente."
  # Ancien mot de passe valide
    When I fill in "Ancien mot de passe" with "emmanuel.risler.pro@gmail.com"
    And I click "Enregistrer"
    Then the field "Nouveau mot de passe (confirmation)" should have error: "Cette saisie du nouveau mot de passe n'est pas identique à la précédente."
  # Second mot de passe identique au premier
    When I fill in "Nouveau mot de passe (confirmation)" with "aaaaaaaa"
    And I click "Enregistrer"
    Then the following message is shown and closed: "Modification effectuée."
  # Déconnexion
    When I click "currentUserButton"
    And I click "Déconnexion"
    Then the following message is shown and closed: "Vous n'êtes pas connecté."
  # Reconnexion avec le nouveau mot de passe
    When I fill in "email" with "emmanuel.risler.pro@gmail.com"
    And I fill in "password" with "aaaaaaaa"
    And I click "connection"
    Then I should see "Vous ne disposez d'aucun droit d'accès à une unité organisationnelle."
