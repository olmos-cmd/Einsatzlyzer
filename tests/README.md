# Compatibility test matrix

Run these checks on a staging WordPress installation before a release:

1. No SEO plugin: Einsatzlyzer outputs one `NewsArticle` and one complete `BreadcrumbList`.
2. Yoast SEO: one breadcrumb list, all three items contain URLs, no `Event` node.
3. Rank Math: normalized `NewsArticle`, one complete breadcrumb list, no `Event` node.
4. All in One SEO: normalized schema output, one complete breadcrumb list, no `Event` node.
5. SEOPress: article and breadcrumb filters apply and event schema is removed.
6. Incident with featured image at least 1200 × 675 pixels reports a valid image.
7. Incident without a featured image uses the first gallery image or configured default image.
8. German and English plugin interfaces contain no untranslated diagnostic labels.
9. Published, draft, noindex and custom-canonical incidents show the correct indexing status.
10. Updating from an earlier version keeps the cache notice visible until explicitly confirmed.

The `schema-integration-test.php` file is a lightweight fixture check. Full integration tests require WordPress and the respective SEO plugins.

## Version 10.4.1 checks

- Disable each print option separately and verify the corresponding section is absent from browser print preview.
- Verify website custom logo is shown only when the print-logo option is enabled.
- Export one year as PDF, CSV and XLSX and verify totals and row count against the backend list.
- Open XLSX in Excel or LibreOffice and verify columns, umlauts and automatic filter.
- Verify annual report export requires `manage_options` and a valid WordPress nonce.


## Version 10.4.2 checks

- Start “Fetch missing automatically” with a mix of complete and incomplete reports.
- Confirm reports with existing weather are skipped in missing-only mode.
- Confirm “Refresh all” processes reports with existing weather again.
- Force one API failure and confirm it appears in the error log and in “Retry errors”.
- Pause and resume a running batch without processing an incident twice.
- Stop a running batch and confirm the interface returns to an idle state.
- Test at least 260 reports and confirm no single PHP request processes the complete queue.


## Version 10.4.3 checks

Run `php tests/mobile-gallery-test.php` to verify the compact mobile incident image heading and spacing.

- `routing-backup-translation-test.php`: validates schema 6 routing backup fields and English distance-interface strings.
