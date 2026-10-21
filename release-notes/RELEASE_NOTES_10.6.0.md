# Einsatzlyzer 10.6.0

## Deutsch

Diese Version ergänzt eine zentrale Fahrzeugverwaltung und einen sicheren Import von Einsatzdepeschen. Fahrzeuge werden vom Benutzer mit Funkrufname, Fahrzeugbezeichnung und Ortswehr gepflegt. Beim Depeschen-Import werden nur eindeutig hinterlegte eigene Fahrzeuge automatisch zugeordnet. Unbekannte Funkrufnamen bleiben unzugeordnet.

Vor dem Speichern erscheint immer eine Vergleichsansicht. Leere Felder können übernommen werden; bei vorhandenen oder abweichenden Angaben entscheidet der Benutzer. Eine wahrscheinlich falsche Depesche wird deutlich markiert. Die interne Einsatznummer bleibt getrennt von der Leitstellen-Einsatznummer.

Der vollständige ZIP-Import und -Export enthält nun außerdem Fahrzeugstammdaten, Funkrufnamen, Aktivstatus, Fahrzeugbilder und Depeschen-Metadaten.

PDF-Text wird direkt in PHP ausgelesen. `pdftotext` ist nur noch ein optionaler Fallback. Reine Scan-PDFs benötigen OCR.

## English

This release adds central vehicle management and safe dispatch PDF importing. Vehicles are maintained by the user with call sign, designation and station. Only explicitly configured own vehicles are matched automatically. Unknown call signs remain unassigned.

A comparison screen is always shown before saving. Empty fields can be filled, while existing or differing values require a user decision. A likely mismatched dispatch is clearly flagged. Internal incident numbers remain separate from control-center incident numbers.

Full ZIP backup and restore now also includes vehicle master data, call signs, active status, vehicle images and dispatch metadata.
