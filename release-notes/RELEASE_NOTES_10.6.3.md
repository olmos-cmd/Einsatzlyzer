# Einsatzlyzer 10.6.4

## Deutsch

Diese Fehlerkorrektur hält den Depeschen-Import beim geöffneten Einsatz und schützt vertrauliche Leitstellenkennungen konsequent.

- Die Importvorschau erscheint direkt im geöffneten Einsatzeditor.
- Die Leitstellen-Einsatznummer wird nicht in den Einsatz übernommen und nicht öffentlich ausgegeben.
- In der Vorschau wird die Kennung nur maskiert angezeigt.
- Intern wird ausschließlich ein nicht rückrechenbarer Prüfwert gespeichert.
- Bereits vorhandene Klartextwerte werden einmalig entfernt.
- Jahresberichte, Frontend und öffentliche Exporte bleiben frei von Leitstellenkennungen.

## English

This maintenance release keeps dispatch review attached to the open incident and strictly protects confidential control-center identifiers.

- The import preview is displayed directly in the open incident editor.
- The control-center incident number is not imported into the incident and is never publicly exposed.
- The identifier is shown only in masked form during review.
- Only a non-reversible verification hash is stored internally.
- Existing clear-text values are removed during a one-time migration.
- Annual reports, frontend output and public exports remain free of control-center identifiers.
