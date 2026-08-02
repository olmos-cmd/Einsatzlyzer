# Einsatzlyzer 10.6.2

## Deutsch
Der Depeschen-Import benötigt kein externes Serverprogramm mehr. Einsatzlyzer enthält jetzt einen eigenen PHP-PDF-Textparser für textbasierte Einsatzdepeschen. Falls `pdftotext` auf dem Server vorhanden ist, wird es nur noch als optionaler Fallback verwendet. Reine Scan-PDFs ohne eingebetteten Text können weiterhin nicht ohne OCR ausgelesen werden und erhalten eine verständliche Fehlermeldung.

## English
Dispatch importing no longer requires an external server binary. Einsatzlyzer now bundles a pure-PHP PDF text extractor for text-based dispatch PDFs. If `pdftotext` is available, it is used only as an optional fallback. Image-only scanned PDFs still require OCR and now produce a clear error message.
