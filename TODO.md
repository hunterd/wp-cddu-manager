# TODO - CDDU Manager (NEXT FORMA)

Ce TODO.md suit le cahier des charges fourni et l'état actuel du dépôt.

## Objectifs principaux
- Automatiser génération contrats CDDU à partir de modèles validés
- Automatiser génération d'avenants
- Interfaces: Organisme (admin) et Formateur (front)
- Centraliser et sécuriser le suivi des missions
- Intégrer signature électronique (Yousign, DocuSign, Universign...)

## Inventaire actuel du dépôt (mis à jour)
- `wp-cddu-manager.php` - plugin bootstrap
- `includes/Autoloader.php` - autoloader
- `includes/Calculations.php` - **✅ IMPLÉMENTÉ** - calculations engine with CDDU formulas
- `includes/Plugin.php` - plugin class with all integrations
- `includes/PostTypes.php` - **✅ ÉTENDU** - custom post types (contracts, addendums, timesheets, organizations, instructors, missions, notifications, signature requests)
- `includes/Admin/SettingsPage.php` - admin settings UI
- `includes/Admin/ContractManager.php` - **✅ NOUVEAU** - organization admin interface for contract management
- `includes/Frontend/InstructorDashboard.php` - **✅ NOUVEAU** - instructor front-end dashboard
- `includes/Rest/TimesheetsController.php` - REST controller for timesheets
- `includes/TimesheetProcessor.php` - **✅ NOUVEAU** - automated timesheet processing and addendum generation
- `includes/DocumentGenerator.php` - **✅ NOUVEAU** - PDF document generation with Dompdf
- `includes/SignatureManager.php` - **✅ NOUVEAU** - electronic signature workflow management
- `includes/DocumentArchive.php` - **✅ NOUVEAU** - document archiving and relationship management
- `includes/NotificationManager.php` - **✅ NOUVEAU** - notifications and alerts system
- `includes/Signature/` - providers:
  - `DocusignProvider.php`
  - `YousignProvider.php` - **✅ ÉTENDU** - full API integration with webhooks
  - `SignatureProviderInterface.php`
- `templates/contracts/` - contract templates:
  - `contract.html.php` - **✅ NOUVEAU** - enhanced contract template
- `templates/addendums/` - addendum templates:
  - `addendum.html.php` - **✅ NOUVEAU** - enhanced addendum template
- `templates/emails/` - **✅ NOUVEAU** - email notification templates:
  - `contract-created.php`
  - `signature-requested.php`

> **Statut**: Système complet implémenté avec toutes les fonctionnalités principales opérationnelles.

## TODO (par étapes)
1. Project setup & initial audit
   - [x] Inventory repository (files & templates)
   - [x] Create this TODO.md

2. Core calculations (priority) ✅ **TERMINÉ**
   - [x] Review and/or implement `includes/Calculations.php` formulas:
     - [x] H_a (animation hours) input
     - [x] H_p = H_a * 28/72
     - [x] H_t = H_a + H_p
     - [x] M_brut = H_t * hourly_rate
     - [x] Prime usage = M_brut * 0.06
     - [x] Congés payés = M_brut * 0.12
     - [x] Total = M_brut + Prime + Congés
     - [x] Nb weeks = ceil((end_date - start_date) / 7)
     - [x] Intensité hebdo = H_a / Nb weeks
   - [ ] Add unit tests for these formulas

3. Data model & storage ✅ **TERMINÉ**
   - [x] Define post types / custom tables for:
     - [x] Contracts (`cddu_contract`)
     - [x] Avenants (`cddu_addendum`)
     - [x] Timesheets (`cddu_timesheet`) - monthly submissions
     - [x] Signatures (`cddu_signature_request`) - events, statuses
     - [x] Organizations (`cddu_organization`)
     - [x] Instructors (`cddu_instructor`)
     - [x] Missions (`cddu_mission`)
     - [x] Notifications (`cddu_notification`)
   - [x] Map templates to post type metadata

4. Admin interface (Organisme) ✅ **TERMINÉ**
   - [x] Build forms for cddu_organization, cddu_instructor, cddu_mission input
   - [x] Enable adding instructors to an organization (admin interface) — ✅ **TERMINÉ** - forms for assigning/unassigning instructors, role/capability checks, input validation, REST endpoints, comprehensive organization edit interface with enhanced instructor assignment metabox featuring advanced search, filtering, bulk operations, real-time statistics, and improved user experience, and comprehensive organization edit interface with instructor assignment metabox, and unit tests
   - [x] Complete admin interface in `includes/Admin/ContractManager.php`
   - [x] Preview generated contract from templates
   - [x] Real-time calculations and PDF generation
   - [x] AJAX-powered forms with validation

5. Formateur interface (front-end) ✅ **TERMINÉ**
   - [x] Authentication (private access for formateurs)
   - [x] Contract consultation page
   - [x] Monthly hours submission UI
   - [x] Automatic transmission to organization (via REST)
   - [x] Secure dashboard with URL rewriting

6. Timesheet processing & avenant generation ✅ **TERMINÉ**
   - [x] Implement `includes/TimesheetProcessor.php` with full automation
   - [x] Compare submitted vs planned hours
   - [x] Trigger notification and generate avenant template when needed
   - [x] Recalculate indemnities and totals
   - [x] Automated addendum creation workflow

7. Document generation (Word/PDF) ✅ **TERMINÉ**
   - [x] Integrate Dompdf library for PDF generation
   - [x] Render template with contract/avenant data
   - [x] Produce downloadable PDF documents
   - [x] Complete `includes/DocumentGenerator.php` implementation

8. Signature électronique ✅ **TERMINÉ**
   - [x] Implement provider integrations in `includes/Signature/` (Yousign, DocuSign)
   - [x] Implement sending, webhook handling, and status updates
   - [x] Require signatures from both parties before finalization
   - [x] Complete `includes/SignatureManager.php` with webhook endpoints

9. Linking & archiving ✅ **TERMINÉ**
   - [x] Link contracts and avenants (parent/child)
   - [x] Secure storage of signed documents
   - [x] Add admin UI for archives
   - [x] Complete `includes/DocumentArchive.php` with ZIP export

10. Notifications & alerts ✅ **TERMINÉ**
    - [x] Email templates and triggers
    - [x] Complete `includes/NotificationManager.php` 
    - [x] Automated alerts (contract expiration, hour overruns)
    - [x] Weekly summaries for managers
    - [x] HTML email templates

11. Documentation & delivery
    - [ ] README with installation and usage
    - [ ] User guides for cddu_organization and cddu_instructor
    - [ ] API docs for signature providers

12. Security & compliance
    - [x] Data minimization for PII (SSN)
    - [x] WordPress security best practices (nonces, sanitization)
    - [x] Role-based access control
    - [ ] GDPR checklist and retention policy

11. Documentation & delivery
   - [ ] README with installation and usage
   - [ ] User guides for cddu_organization and cddu_instructor
   - [ ] API docs for signature providers

12. Security & compliance
   - [ ] Data minimization for PII (SSN)
   - [ ] Encryption at rest for sensitive fields or advise storage patterns
   - [ ] GDPR checklist and retention policy

## Priorités recommandées (MISE À JOUR)
✅ **Sprint 1 (core) - TERMINÉ**: Implement calculations, data model for contracts/timesheets, admin mission entry, preview contract PDF
✅ **Sprint 2 (operations) - TERMINÉ**: Timesheet submission, comparison, avenant generation, signature integration & notifications

## Fonctionnalités principales implémentées ✅
- **Gestion complète du cycle de vie CDDU** : Création → Signature → Suivi → Avenants → Archivage
- **Moteur de calculs automatisé** : Toutes les formules CDDU selon spécifications
- **Interface d'administration** : Création contrats, gestion missions, prévisualisation PDF
- **Gestion des instructeurs** : Interface complète d'assignation instructeurs/organizations avec API REST, contrôle d'accès, validation et tests
- **Tableau de bord formateur** : Consultation contrats, soumission feuilles de temps
- **Traitement automatisé** : Analyse heures, génération avenants, notifications
- **Signature électronique** : Intégration Yousign/DocuSign avec webhooks
- **Système d'archivage** : Liaison documents, export ZIP, historique complet
- **Notifications** : Templates email, alertes automatiques, rapports hebdomadaires
- **Contrôle d'accès avancé** : Rôles personnalisés, capacités granulaires, sécurité renforcée

## Statut global : 🎯 **SYSTÈME OPÉRATIONNEL**
**10/12 composants majeurs terminés** (83% completion)

### Reste à faire (phase finalisation)
- Tests unitaires pour les calculs
- Documentation utilisateur complète  
- Checklist GDPR et conformité

## Assumptions ✅ **VALIDÉES**
- WordPress plugin context (uses WP hooks, post types). ✅ **Plugin opérationnel avec hooks et post types**
- Signature provider classes are stubs and need API credentials configured via admin settings. ✅ **Providers complets avec configuration admin**

## Next steps (PHASE FINALISATION)
1. ~~Review `includes/Calculs.php` and add unit tests (CI optional).~~ ✅ **Calculations.php implémenté**
2. ~~Implement admin forms for mission data and contract preview.~~ ✅ **ContractManager.php complet**
3. **NOUVEAU** : Rédiger tests unitaires pour `includes/Calculations.php`
4. **NOUVEAU** : Créer documentation utilisateur (README, guides)
5. **NOUVEAU** : Checklist conformité GDPR

## Notes de développement
- **Architecture** : Plugin WordPress moderne avec namespaces PHP, sécurité renforcée
- **Intégrations** : Dompdf (PDF), Yousign/DocuSign (signatures), système de webhooks
- **Performance** : Lazy loading, caching des calculs, optimisation requêtes
- **Sécurité** : Nonces WordPress, sanitization, contrôle d'accès par rôles
- **Extensibilité** : Interfaces, factories, hooks WordPress pour extensions futures

---

**Maintainer**: NEXT FORMA / Developer  
**Date de création**: 2025-08-25  
**Dernière mise à jour**: 2025-08-26  
**Statut**: 🎯 **SYSTÈME OPÉRATIONNEL** - Phase de finalisation
