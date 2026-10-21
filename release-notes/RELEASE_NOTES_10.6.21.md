# Einsatzlyzer 10.6.21

## Deutsch

### Echte Live-Vorschau für den Vorschaubild-Fokus

Version **10.6.21** verbessert die in 10.6.20 eingeführte Steuerung des Vorschaubild-Ausschnitts. Die Vorschau reagiert jetzt unmittelbar und lässt sich direkt bedienen.

- Das Bild kann direkt in der Vorschau mit Maus oder Finger verschoben werden.
- Beim Ziehen wird automatisch auf **Benutzerdefiniert** umgeschaltet und der X-/Y-Fokus live gespeichert.
- X und Y können zusätzlich über stufenlose Regler eingestellt werden; Prozentfelder und Regler bleiben synchron.
- Neu ausgewählte Bilder erscheinen sofort in der Vorschau, ohne den Einsatz vorher speichern oder die Seite neu laden zu müssen.
- Wird die Galerie umsortiert oder ein Bild entfernt, folgt die Vorschau sofort der tatsächlich verwendeten Bildreihenfolge.
- Auch Änderungen am WordPress-Beitragsbild werden live berücksichtigt.
- Die Vorschau hat dasselbe Seitenverhältnis und dieselbe `cover`-Logik wie die großen Einsatzkarten.

### Kompatibilität und Sicherung

Es werden weiterhin die bestehenden Metafelder `_ffl_preview_focus_mode`, `_ffl_preview_focus_x` und `_ffl_preview_focus_y` verwendet. Es ist keine Datenmigration erforderlich. Backup/Restore und CSV-Export aus 10.6.20 bleiben vollständig kompatibel; das Sicherungsschema bleibt Version 7.

---

## English

### True live preview for preview-image focus

Version **10.6.21** improves the preview-image crop controls introduced in 10.6.20. The preview now reacts immediately and can be manipulated directly.

- Drag the image directly inside the preview using mouse or touch.
- Dragging automatically switches to **Custom** and updates the X/Y focus values live.
- X and Y can also be adjusted with continuous sliders; sliders and percentage fields stay synchronized.
- Newly selected images appear immediately without saving the incident or reloading the page first.
- Reordering or removing gallery images updates the preview immediately to match the actual image order.
- WordPress featured-image changes are also reflected live.
- The preview uses the same aspect ratio and `cover` behavior as the large public incident cards.

### Compatibility and backup

The existing `_ffl_preview_focus_mode`, `_ffl_preview_focus_x` and `_ffl_preview_focus_y` metadata fields remain unchanged. No migration is required. Backup/restore and CSV export remain fully compatible with 10.6.20; backup schema stays at version 7.
