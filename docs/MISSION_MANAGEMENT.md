# Mission Manager

## Vue d'ensemble

La classe `MissionManager` a été créée pour gérer les missions dans le plugin WP CDDU Manager. Elle suit la même architecture que `ContractManager` et offre une interface complète pour créer, modifier, supprimer et gérer les missions.

## Fonctionnalités

### Interface d'administration

Le `MissionManager` ajoute deux pages dans le menu d'administration des missions :

1. **Créer une Mission** (`create-mission`)
   - Formulaire complet pour créer une nouvelle mission
   - Validation en temps réel
   - Calculs automatiques (durée, budget, heures par jour)
   - Gestion des compétences requises

2. **Gérer les Missions** (`manage-missions`)
   - Vue d'ensemble de toutes les missions
   - Filtres par organisation, statut et priorité
   - Tri par colonnes
   - Actions rapides (voir, dupliquer, supprimer)
   - Statistiques en temps réel

### Fonctionnalités AJAX

La classe implémente plusieurs endpoints AJAX :

- `cddu_create_mission` : Création d'une nouvelle mission
- `cddu_update_mission` : Mise à jour d'une mission existante
- `cddu_delete_mission` : Suppression d'une mission (avec vérification des contrats actifs)
- `cddu_get_mission_data` : Récupération des données d'une mission
- `cddu_get_missions_for_organization` : Récupération des missions d'une organisation
- `cddu_validate_mission_data` : Validation des données avant soumission

### Structure des données

Les missions sont stockées avec les métadonnées suivantes :

```php
[
    'organization_id' => int,           // ID de l'organisation
    'title' => string,                  // Titre de la mission
    'description' => string,            // Description détaillée
    'location' => string,               // Lieu de la mission
    'start_date' => string,             // Date de début (Y-m-d)
    'end_date' => string,               // Date de fin (Y-m-d)
    'total_hours' => float,             // Nombre total d'heures
    'hourly_rate' => float,             // Taux horaire
    'required_skills' => array,         // Compétences requises
    'mission_type' => string,           // Type de mission
    'priority' => string,               // Priorité
    'status' => string,                 // Statut
]
```

### Types de mission

- `standard` : Mission standard
- `urgent` : Mission urgente
- `long_term` : Mission long terme
- `part_time` : Mission temps partiel

### Niveaux de priorité

- `low` : Faible
- `medium` : Moyenne
- `high` : Élevée
- `critical` : Critique

### Statuts disponibles

- `draft` : Brouillon
- `open` : Ouverte
- `in_progress` : En cours
- `completed` : Terminée
- `cancelled` : Annulée

## Interface utilisateur

### Formulaire de création

Le formulaire de création inclut :

- Sélection de l'organisation
- Informations de base (titre, description, lieu)
- Planning (dates de début et fin)
- Budget (heures totales, taux horaire)
- Compétences requises (ajout/suppression dynamique)
- Calculs automatiques en temps réel

### Interface de gestion

L'interface de gestion offre :

- Tableau filtrable et triable
- Statistiques en temps réel
- Actions rapides sur chaque mission
- Modal de visualisation détaillée
- Filtres par organisation, statut et priorité

## Validation et sécurité

### Validation des données

- Vérification des champs obligatoires
- Validation des dates (fin > début)
- Validation des valeurs numériques
- Contrôle des permissions utilisateur

### Sécurité

- Utilisation de nonces pour les requêtes AJAX
- Sanitisation de toutes les entrées utilisateur
- Vérification des permissions avant modification
- Protection contre la suppression de missions avec contrats actifs

## Intégration

Le `MissionManager` s'intègre parfaitement avec :

- `ContractManager` : Les missions peuvent être associées aux contrats
- `InstructorManager` : Assignation d'instructeurs aux missions
- `PostTypes` : Utilise le post type `cddu_mission` existant
- `RoleManager` : Respect des permissions utilisateur

## Assets

### JavaScript

Le fichier `mission-manager.js` gère :

- Soumission des formulaires via AJAX
- Gestion dynamique des compétences
- Calculs automatiques
- Filtrage et tri des tables
- Interactions modales

### CSS

Le fichier `mission-manager.css` fournit :

- Styles pour les formulaires
- Design responsive
- Badges de statut et priorité
- Animations et transitions
- Styles pour les modales

## Extensions possibles

Le `MissionManager` peut être étendu pour :

- Assignation automatique d'instructeurs basée sur les compétences
- Notifications automatiques pour les missions critiques
- Intégration avec un calendrier externe
- Génération de rapports de missions
- API REST pour intégrations externes

## Utilisation

```php
// Initialisation automatique dans Plugin.php
new MissionManager();

// Récupération des missions d'une organisation
$missions = get_posts([
    'post_type' => 'cddu_mission',
    'meta_query' => [
        [
            'key' => 'organization_id',
            'value' => $organization_id,
            'compare' => '='
        ]
    ]
]);
```

## Hooks disponibles

Le `MissionManager` utilise les hooks WordPress standard et peut être étendu via :

- Actions avant/après création de mission
- Filtres pour personnaliser les données
- Hooks pour les notifications
- Actions pour l'intégration avec d'autres systèmes
