# Changelog

## 2.0.1 - 2026-07-17

### Behoben

- Ausgabeproblem bei Soft-Hyphens behoben: Das Addon verwendet intern wieder das echte Soft-Hyphen-Zeichen statt des Literal-Strings `&shy;`.
- Bereits gespeicherte Konfigurationswerte wie `&shy;`, `&#173;` und `&#xAD;` werden beim Laden automatisch normalisiert.
- Locale-Auflösung für weitere Sprachen verbessert, damit vorhandene Dictionaries wie `fr`, `it_IT`, `nl_NL`, `sv` oder `es` korrekt gefunden werden.
- HTML-Autodetektion präzisiert, damit Klartext mit Zeichenfolgen wie `<` und `>` nicht fälschlich als Markup verarbeitet wird.

### Dokumentation

- Hinweise für das Feld `Trenn-Symbol` im Backend ergänzt.
- README um Erläuterungen zum Soft-Hyphen und zu erlaubten Eingaben erweitert.

## 2.0.0 - 2026-07-13

### Highlights

- Vollständige Modernisierung des AddOns auf eine aktuelle Architektur.
- Neue primäre API über Namespace `FriendsOfRedaxo\Hyphenator\Hyphenator`.
- HTML5-DOM-basierte Verarbeitung mit `Dom\HTMLDocument` statt Regex-basierter Roh-HTML-Manipulation.
- Fokus auf barriereärmere Ausgabe und kontrollierbare Ausschlussregeln.

### Neue Features

- Namespaced API als neuer Standard:
  - `FriendsOfRedaxo\Hyphenator\Hyphenator::hyphenate()`
  - `FriendsOfRedaxo\Hyphenator\Hyphenator::hyphenateHtml()`
  - `FriendsOfRedaxo\Hyphenator\Hyphenator::hyphenateText()`
- Legacy-Bridge bleibt erhalten:
  - `class.hyphenator.php` delegiert auf die neue Namespaced-Class.
- Sprachbehandlung verbessert:
  - Locale-Normalisierung mit `ext-intl` (`Locale::canonicalize`, `Locale::parseLocale`).
  - Sprachableitung pro HTML-Subtree über `lang`-Attribute.
- Ausschlüsse deutlich erweitert:
  - Ausgeschlossene HTML-Tags.
  - Ausgeschlossene CSS-Klassen.
  - Ausgeschlossene freie CSS-Selektoren (inkl. `#id`, `.klasse`, Pseudo-Selektoren).
  - Ausgeschlossene Wörter (case-insensitive, zeilenweise oder kommagetrennt).
  - Elementbasierte Opt-out-Attribute:
    - `data-hyphenator-ignore`
    - `data-hyphenator="off"`
- Neue Demo-Seite im Backend:
  - Lange Testwörter und Fließtexte.
  - Vorher/Nachher-Vergleich.
  - Gerenderte Vorschau mit horizontaler Resize-Möglichkeit.
  - Technische Markup-Ausgabe für Debugging.

### Backend / Konfiguration

- Konfigurationsseite erweitert um neue Felder:
  - CSS-Selektor-Ausschlüsse.
  - Wort-Ausschlüsse.
- Verbesserte Eingabevalidierung:
  - `leftMin` auf 1-10.
  - `rightMin` auf 1-10.
  - `wordMin` auf 2-50.
- CSRF-Schutz beim Speichern der Konfiguration ergänzt.

### Stabilität und Kompatibilität

- Deprecation-Fixes für PHP 8.4:
  - Null-sichere Verarbeitung von DOM-Attributwerten (`trim`/`strtolower`-Warnings behoben).
- Defensive Behandlung ungültiger CSS-Selektoren in Ausschlusslisten.

### Anforderungen

- REDAXO >= 5.19.0
- PHP >= 8.4
- `ext-intl`
- Composer-Dependency: `org_heigl/hyphenator` ^3.1

### Dokumentation

- README umfassend aktualisiert:
  - Migration auf Namespace-API.
  - Locale-Erkennung im Template.
  - Ausschlussmöglichkeiten für Tags, Klassen, Selektoren und Wörter.
  - Hinweise zur Demo und zur tatsächlichen Vorschauausgabe.
