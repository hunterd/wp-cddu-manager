# TODO - CDDU Manager (NEXT FORMA)

Ce TODO.md suit le cahier des charges fourni et l'état actuel du dépôt.

## Objectifs principaux
- Automatiser génération contrats CDDU à partir de modèles validés
- Automatiser génération d'avenants
- Interfaces: Organisme (admin) et Formateur (front)
- Centraliser et sécuriser le suivi des missions
- Intégrer signature électronique (Yousign, DocuSign, Universign...)

## Inventaire actuel du dépôt (extraits)
- `wp-cddu-manager.php` - plugin bootstrap
- `includes/Autoloader.php` - autoloader
- `includes/Calculs.php` - calculations (à vérifier)
- `includes/Plugin.php` - plugin class (fichier ouvert)
- `includes/PostTypes.php` - custom post types
- `includes/Admin/SettingsPage.php` - admin settings UI
- `includes/Rest/TimesheetsController.php` - REST controller for timesheets
- `includes/Signature/` - providers:
  - `DocusignProvider.php`
  - `YousignProvider.php`
  - `SignatureProviderInterface.php`
- `templates/contracts/cddu.html.php` - contract template
- `templates/addendums/avenant.html.php` - addendum template

> Remarque: l'inventaire a été construit à partir des fichiers fournis; il peut manquer d'autres fichiers non listés.

## TODO (par étapes)
1. Project setup & initial audit
   - [x] Inventory repository (files & templates)
   - [x] Create this TODO.md

2. Core calculations (priority)
   - [ ] Review and/or implement `includes/Calculs.php` formulas:
     - H_a (animation hours) input
     - H_p = H_a * 28/72
     - H_t = H_a + H_p
     - M_brut = H_t * hourly_rate
     - Prime usage = M_brut * 0.06
     - Congés payés = M_brut * 0.12
     - Total = M_brut + Prime + Congés
     - Nb weeks = ceil((end_date - start_date) / 7)
     - Intensité hebdo = H_a / Nb weeks
   - [ ] Add unit tests for these formulas

3. Data model & storage
   - [ ] Define post types / custom tables for:
     - Contracts
     - Avenants
     - Timesheets (monthly submissions)
     - Signatures (events, statuses)
   - [ ] Map templates to post type metadata

4. Admin interface (Organisme)
   - [ ] Build forms for organisme, formateur, mission input
   - [ ] Use `includes/Admin/SettingsPage.php` as starting point
   - [ ] Preview generated contract from `templates/contracts/cddu.html.php`

5. Formateur interface (front-end)
   - [ ] Authentication (private access for formateurs)
   - [ ] Contract consultation page
   - [ ] Monthly hours submission UI
   - [ ] Automatic transmission to organism (via REST)

6. Timesheet processing & avenant generation
   - [ ] Implement `includes/Rest/TimesheetsController.php` endpoints (store, validate)
   - [ ] Compare submitted vs planned hours
   - [ ] Trigger notification and generate avenant template when needed
   - [ ] Recalculate indemnities and totals

7. Document generation (Word/PDF)
   - [ ] Integrate a templating-to-PDF/Word library (e.g., Dompdf, PhpWord)
   - [ ] Render template with contract/avenant data
   - [ ] Produce downloadable PDF/Word

8. Signature électronique
   - [ ] Implement provider integrations in `includes/Signature/` (Yousign, DocuSign, Universign)
   - [ ] Implement sending, webhook handling, and status updates
   - [ ] Require signatures from both parties before finalization

9. Linking & archiving
   - [ ] Link contracts and avenants (parent/child)
   - [ ] Secure storage of signed documents
   - [ ] Add admin UI for archives

10. Notifications & alerts
   - [ ] Email templates and triggers
   - [ ] Optional Slack/Teams notifications

11. Documentation & delivery
   - [ ] README with installation and usage
   - [ ] User guides for Organisme and Formateur
   - [ ] API docs for signature providers

12. Security & compliance
   - [ ] Data minimization for PII (SSN)
   - [ ] Encryption at rest for sensitive fields or advise storage patterns
   - [ ] GDPR checklist and retention policy

## Priorités recommandées (first 2 sprints)
Sprint 1 (core): Implement calculations, data model for contracts/timesheets, admin mission entry, preview contract PDF
Sprint 2 (operations): Timesheet submission, comparison, avenant generation, basic signature stub & notifications

## Assumptions
- WordPress plugin context (uses WP hooks, post types). Confirm plugin activation/DB access when implementing DB schema.
- Signature provider classes are stubs and need API credentials configured via admin settings.

## Next steps (short)
1. Review `includes/Calculs.php` and add unit tests (CI optional).
2. Implement admin forms for mission data and contract preview.

---

Maintainer: NEXT FORMA / Developer
Date: 2025-08-25
