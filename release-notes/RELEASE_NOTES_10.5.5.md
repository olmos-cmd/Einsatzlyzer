# Einsatzlyzer 10.5.5

## Deutsch

Diese Version räumt die Administration auf und reduziert den Cache-Hinweis auf den tatsächlich notwendigen Moment nach einem Update.

- Der projektspezifische Menüpunkt **Werkzeuge** wurde vollständig entfernt.
- Die einmalige Funktion zum Entfernen eines früheren Standardhinweises wurde einschließlich Handler und Import-Sonderbehandlung entfernt.
- Der Update-Hinweis wird nur noch auf Einsatzlyzer-Seiten und nur einmal pro Version angezeigt.
- Die verfügbaren Aktionen lauten **Unterstützte Caches leeren** und **Später**.
- Nach dem Leeren wird eine kurze Erfolgsmeldung angezeigt.
- Die Erkennung nennt nur konkrete Cache-Plugins; nginx-, Hoster- und CDN-Caches bleiben weiterhin separat in der Diagnose erläutert.
- Die dauerhafte manuelle Cache-Schaltfläche bleibt unter **Einsatzlyzer → Diagnose** verfügbar.

## English

This release cleans up the administration and limits the cache notice to the moment it is actually needed after an update.

- Removed the project-specific **Tools** menu completely.
- Removed the one-off legacy standard-notice cleanup, its handler and its import-specific text mutation.
- The update notice is shown only on Einsatzlyzer screens and only once per version.
- The available actions are **Clear supported caches** and **Later**.
- A short success message is shown after clearing.
- Detection lists only concrete cache plugins; nginx, hosting and CDN caches remain explained separately in Diagnostics.
- Permanent manual cache clearing remains available under **Einsatzlyzer → Diagnostics**.
