# Guide d'utilisation - Association Formateurs/Organismes

## Nouvelle fonctionnalité implémentée ✅

Vous pouvez maintenant associer des formateurs à un organisme directement depuis l'interface d'édition de l'organisme.

## Comment utiliser cette fonctionnalité

### 1. Accéder à l'organisme
1. Allez dans le menu **Organismes** dans l'admin WordPress
2. Cliquez sur **Modifier** pour un organisme existant ou créez un nouvel organisme

### 2. Associer des formateurs
Dans la sidebar droite, vous trouverez une nouvelle metabox **"Assigned Instructors"** avec :

- **Zone de recherche** : Recherchez rapidement parmi tous les formateurs
- **Liste des formateurs** : Cochez les formateurs à associer à cet organisme
- **Boutons d'action** :
  - **Select All** : Sélectionner tous les formateurs visibles
  - **Deselect All** : Désélectionner tous les formateurs visibles
- **Compteur** : Affiche le nombre de formateurs sélectionnés

### 3. Fonctionnalités avancées

#### Recherche intelligente
- Tapez dans la zone de recherche pour filtrer les formateurs par nom ou email
- La recherche fonctionne en temps réel
- Un bouton "Clear" permet d'effacer la recherche

#### Interface visuelle
- Les formateurs assignés apparaissent avec un arrière-plan vert
- L'interface est responsive et s'adapte aux petits écrans
- Maximum 300px de hauteur avec défilement automatique

#### Sauvegarde automatique
- Les associations sont sauvegardées automatiquement lors de l'enregistrement de l'organisme
- La relation est bidirectionnelle : formateur ↔ organisme
- Validation automatique des IDs de formateurs

## Données techniques

### Métadonnées sauvegardées
- **Organisme** : `assigned_instructors` (array d'IDs de formateurs)
- **Formateur** : `assigned_organizations` (array d'IDs d'organismes)

### Permissions requises
- Capacité `cddu_manage_instructors` ou `manage_options`
- Vérification de nonce pour la sécurité

### API REST disponible
Le système utilise déjà l'API REST existante :
- `POST /wp-json/cddu-manager/v1/instructor-organizations/assign`
- `DELETE /wp-json/cddu-manager/v1/instructor-organizations/unassign`
- `GET /wp-json/cddu-manager/v1/instructor-organizations/{org_id}/instructors`

## Validation et sécurité
- ✅ Validation des IDs de formateurs
- ✅ Vérification des permissions utilisateur
- ✅ Protection CSRF avec nonces WordPress
- ✅ Sanitisation des données
- ✅ Validation des types de post

## Tests inclus
- ✅ Test de rendu de la metabox
- ✅ Test de sauvegarde des associations
- ✅ Test de validation des données
- ✅ Couverture des cas d'erreur

## Intégration avec le système existant
Cette fonctionnalité s'intègre parfaitement avec :
- Le gestionnaire d'instructeurs existant (`InstructorManager`)
- Le contrôleur REST (`InstructorOrganizationController`)
- Le système de rôles et permissions (`RoleManager`)
- L'interface de création de contrats

## Prochaines étapes recommandées
1. Tester en environnement de développement
2. Former les utilisateurs administrateurs
3. Documenter les processus métier
4. Prévoir la migration des données existantes si nécessaire
