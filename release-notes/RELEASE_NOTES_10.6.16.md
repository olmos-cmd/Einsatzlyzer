# Einsatzlyzer 10.6.16

## Deutsch

- Zentrale URL-Normalisierung ausschließlich für Einsatzlyzer-eigene Einsatzbericht-Links ergänzt.
- Einsatzlyzer gibt interne Einsatz-URLs, Canonical-/OpenGraph-URLs, Schema-URLs, Kartenlinks, Teilen-Links und CSV-Links konsistent mit abschließendem `/` aus.
- WordPress-Permalinkregeln, andere Inhaltstypen und die bestehende Sitemap-Konfiguration werden nicht global verändert.
- SEO-Diagnose vergleicht Canonical-URLs jetzt exakt; `/einsatz` und `/einsatz/` gelten nicht mehr als identisch.
- Query-Strings und URL-Fragmente bleiben bei der Normalisierung erhalten.

## English

- Added centralized URL normalization only for Einsatzlyzer-generated incident report links.
- Internal incident URLs, canonical/OpenGraph URLs, schema URLs, map links, share links and CSV links are now consistently emitted with a trailing `/`.
- WordPress permalink rules, other post types and the existing sitemap configuration are not modified globally.
- SEO diagnostics now compare canonical URLs exactly; `/incident` and `/incident/` are no longer treated as identical.
- Query strings and URL fragments are preserved during normalization.
