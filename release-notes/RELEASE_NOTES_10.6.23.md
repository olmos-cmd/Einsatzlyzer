# Einsatzlyzer 10.6.23 – zuverlässige Live-Vorschau für den Vorschaubild-Fokus

## Deutsch

Version 10.6.23 korrigiert die Live-Vorschau des pro Einsatz einstellbaren Vorschaubild-Fokus. In 10.6.22 konnte sich das Fadenkreuz zwar bewegen, während der sichtbare Kartenausschnitt im WordPress-Editor je nach Browser bzw. Bildformat scheinbar unverändert blieb.

Die Karten-Vorschau wird deshalb nicht mehr nur über CSS `object-position` gesteuert. Einsatzlyzer berechnet nun aus den tatsächlichen Bildmaßen und dem Kartenformat den `cover`-Zuschnitt und verschiebt das Vorschaubild pixelgenau. Dadurch ist jede tatsächlich mögliche Fokusverschiebung sofort sichtbar.

Zusätzlich zeigt ein Hinweis an, ob das aktuelle Bild in der Kartenform horizontal, vertikal, in beide Richtungen oder aufgrund eines nahezu identischen Seitenverhältnisses kaum verschoben werden kann. Das erklärt insbesondere Fälle, in denen eine Achse technisch keinen sichtbaren Spielraum besitzt.

Gespeicherte Fokuswerte, Frontend-Ausgabe und vollständige Sicherungen bleiben kompatibel. Das Backup-Schema bleibt Version 7.

## English

Version 10.6.23 fixes the live preview for the per-incident preview-image focus. In 10.6.22 the crosshair could move while the visible card crop in the WordPress editor could still appear unchanged depending on the browser or image aspect ratio.

The card preview no longer relies solely on CSS `object-position`. Einsatzlyzer now calculates the actual `cover` crop from the image and card dimensions and moves the preview image pixel-perfectly, making every available focus movement immediately visible.

A new hint also explains whether the current image can move horizontally, vertically, in both directions, or barely at all because the image and card aspect ratios are nearly identical.

Stored focus values, frontend output and full backups remain compatible. Backup schema stays at version 7.
