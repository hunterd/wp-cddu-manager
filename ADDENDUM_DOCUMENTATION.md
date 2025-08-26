# Système d'Avenants - CDDU Manager

## Vue d'ensemble

Le système d'avenants permet de créer des avenants aux contrats CDDU existants, en utilisant le modèle spécifique NEXT FORMA conforme au document fourni.

## Nouvelles fonctionnalités

### 1. Modèles d'avenants

2 nouveaux modèles d'avenants ont été créés :

- **`addendum-next-forma.html.php`** : Modèle complet NEXT FORMA avec tous les champs détaillés
- **`addendum-template.html`** : Modèle générique avec variables dynamiques

### 2. Formulaire de création d'avenant

Nouveau formulaire dédié accessible via le menu d'administration :
- **URL** : `admin.php?page=create-addendum`
- **Localisation** : Contrats CDDU → Créer un avenant

#### Sections du formulaire :

1. **Informations du contrat de base**
   - Sélection du contrat original
   - Numéro d'avenant

2. **Détails de l'organisation**
   - Nom, adresse, RCS
   - Informations du gérant
   - Pré-rempli avec les valeurs NEXT FORMA

3. **Détails du formateur**
   - Informations personnelles complètes
   - Classification professionnelle
   - Auto-remplissage depuis les données utilisateur

4. **Modifications contractuelles**
   - Dates de contrat (original et nouveau)
   - Taux horaire
   - Dates d'effet et de signature

5. **Répartition mensuelle des heures**
   - Heures totales, AF (Acte de Formation), PR (Préparation et Recherches)
   - Calcul automatique des montants
   - Pré-configuré pour Nov 2023, Déc 2023, Jan 2024

6. **Planning hebdomadaire (optionnel)**
   - Ajout dynamique de semaines
   - Répartition AF/PR par semaine

### 3. Fonctionnalités JavaScript

#### Fichier : `assets/js/addendum-manager.js`

- **Calculs automatiques** : Montants mensuels et totaux
- **Auto-remplissage** : Données instructeur et contrat via AJAX
- **Gestion dynamique** : Ajout/suppression de semaines
- **Prévisualisation** : Génération d'aperçu PDF
- **Validation** : Contrôles de cohérence des données

### 4. Styles CSS

#### Fichier : `assets/css/addendum-form.css`

- Interface cohérente avec l'administration WordPress
- Responsive design pour mobiles et tablettes
- Styles spécifiques pour les sections mensuelles et hebdomadaires
- Indicateurs visuels et états de chargement

### 5. Nouvelles méthodes AJAX

#### ContractManager.php

- **`ajax_generate_addendum()`** : Création de l'avenant
- **`ajax_preview_addendum()`** : Prévisualisation de l'avenant
- **`ajax_get_instructor_data()`** : Récupération des données formateur
- **`ajax_get_contract_data()`** : Récupération des données contrat

### 6. Intégration au formulaire principal

Le formulaire de création de contrat a été enrichi :

- **Sélecteur de type** : Contrat normal vs Avenant
- **Redirection automatique** : Vers le formulaire d'avenant dédié
- **Option "Continuer ici"** : Pour créer un avenant avec le formulaire standard
- **Modèle d'avenant** : Chargement automatique du template d'avenant

## Variables disponibles dans les modèles

### Organisation
```php
{{org.name}}                // Nom de l'organisation
{{org.rcs_city}}           // Ville du RCS
{{org.rcs_number}}         // Numéro RCS
{{org.address}}            // Adresse complète
{{org.manager_title}}      // Titre du gérant (M./Mme)
{{org.manager_name}}       // Nom du gérant
{{org.manager_role}}       // Fonction du gérant
{{org.city}}               // Ville
```

### Formateur
```php
{{instructor.gender}}               // M./Mme
{{instructor.full_name}}           // Nom complet
{{instructor.birth_date}}          // Date de naissance
{{instructor.birth_place}}         // Lieu de naissance
{{instructor.address}}             // Adresse
{{instructor.social_security}}     // N° Sécurité Sociale
{{instructor.job_title}}           // Intitulé du poste
{{instructor.classification_level}} // Niveau de classification
{{instructor.coefficient}}         // Coefficient
```

### Avenant
```php
{{addendum.number}}                 // Numéro d'avenant
{{addendum.original_contract_date}} // Date du contrat original
{{addendum.original_end_date}}      // Date de fin originale
{{addendum.new_end_date}}          // Nouvelle date de fin
{{addendum.effective_date}}        // Date d'effet
{{addendum.signature_date}}        // Date de signature
{{addendum.monthly_breakdown}}     // Répartition mensuelle
{{addendum.weekly_schedule}}       // Planning hebdomadaire
```

### Mission
```php
{{mission.hourly_rate}}    // Taux horaire
{{mission.end_date}}       // Date de fin
```

### Contrat
```php
{{contract.original_date}}     // Date du contrat original
{{contract.original_end_date}} // Date de fin originale
```

## Utilisation

### 1. Création d'un avenant

1. Aller dans **Contrats CDDU → Créer un avenant**
2. Sélectionner le contrat original (optionnel)
3. Remplir les informations de l'organisation
4. Sélectionner ou saisir les informations du formateur
5. Définir les modifications contractuelles
6. Configurer la répartition mensuelle des heures
7. Ajouter un planning hebdomadaire si nécessaire
8. Calculer les valeurs et prévisualiser
9. Générer l'avenant PDF

### 2. Depuis le formulaire de contrat principal

1. Aller dans **Contrats CDDU → Créer un contrat**
2. Sélectionner "Avenant" dans le type de document
3. Choisir de rediriger vers le formulaire dédié ou continuer

### 3. Prévisualisation et génération

- **Calcul** : Bouton "Calculer les valeurs" pour afficher les montants
- **Aperçu** : Bouton "Prévisualiser l'avenant" pour ouvrir dans une nouvelle fenêtre
- **Génération** : Bouton "Générer l'avenant PDF" pour créer le document final

## Structure des fichiers

```
wp-cddu-manager/
├── templates/
│   └── addendums/
│       ├── addendum-next-forma.html.php    # Modèle NEXT FORMA complet
│       └── addendum-template.html          # Modèle générique
├── templates/admin/
│   └── create-addendum-form.php            # Formulaire de création
├── assets/
│   ├── js/
│   │   └── addendum-manager.js             # JavaScript d'avenant
│   └── css/
│       └── addendum-form.css               # Styles d'avenant
└── includes/Admin/
    └── ContractManager.php                 # Logique serveur mise à jour
```

## Points techniques

### Auto-remplissage des données

- Les données instructeur sont récupérées via AJAX lors de la sélection
- Les données de contrat original sont chargées automatiquement
- Les calculs sont effectués en temps réel côté client

### Validation des données

- Contrôles de cohérence entre les heures totales, AF et PR
- Validation des dates et montants
- Messages d'erreur explicites

### Responsive design

- Interface adaptée aux écrans mobiles
- Réorganisation des champs sur petits écrans
- Boutons et contrôles accessibles

### Extensibilité

- Structure modulaire pour ajouter d'autres types d'avenants
- Variables de modèle facilement extensibles
- Hooks WordPress respectés

## Conformité

Le modèle NEXT FORMA respecte exactement la structure du document fourni :
- Mentions légales complètes
- Articles numérotés selon le modèle
- Format et présentation identiques
- Variables dynamiques pour personnalisation

## Support

Pour toute question ou modification, consulter la documentation du code ou contacter l'équipe de développement.
