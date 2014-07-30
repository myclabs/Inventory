# Orga

## ACL

### Organisation

Rôles :

- **WorkspaceAdminRole** : admin sur l'organisation, et admin sur la cellule globale

Les rôles des cellules donnent automatiquement le droit de voir l'organisation.

### Cellule

| Droit                     | Observer | Contributor | Manager | Admin |
|:--------------------------|:--------:|:-----------:|:-------:|:-----:|
| Voir une saisie           | X        | X           | X       | X
| Éditer une saisie         |          | X           | X       | X
| Rapport à la collecte     | X        | X           | X       | X
| Voir l'historique         | X        | X           | X       | X
| Voir les commentaires     | X        | X           | X       | X
| Commenter                 |          | X           | X       | X
| Manager les documents     |          | X           | X       | X
| Voir les analyses         | X        |             | X       | X
| Voir les exports          | X        |             | X       | X
| Voir les administrateurs  | X        | X           | X       | X
| Éditer rôles/utilisateurs |          |             |         | X
| Éditer des membres        |          |             |         | X
| Pertinence des cellules   |          |             |         | X
| Rebuild DW                |          |             |         | X
