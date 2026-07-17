Hyphenator
==========

Modernes AddOn für REDAXO 5: Silbentrennung auf Basis von PHP 8.4+.

![Screenshot](https://raw.githubusercontent.com/FriendsOfREDAXO/hyphenator/assets/hyphenator_01.png)

## Funktion

Das Addon verwendet die aktuelle [org_heigl/hyphenator](https://github.com/heiglandreas/Org_Heigl_Hyphenator)-Bibliothek (Version 3.1+) zusammen mit den neuen HTML5-DOM-Tools aus PHP 8.4 (`Dom\HTMLDocument`).

Im HTML-Modus werden nur Textknoten verarbeitet. Strukturell und semantisch sensible Bereiche (z. B. `code`, `pre`, `script`, `style`) bleiben unverändert. So bleibt das Markup intakt und die Ausgabe barriereärmer.

## Was ist für Barrierefreiheit besser als vorher?

- DOM-basierte Verarbeitung statt Regex auf Roh-HTML: Attribute, Semantik und Struktur bleiben erhalten.
- Ausschluss sensibler Bereiche per Tag, Klasse, freiem CSS-Selektor und Element-Attribut (`data-hyphenator-ignore`, `data-hyphenator="off"`).
- Automatische Spracherkennung pro Element über `lang`-Attribute im HTML-Baum.
- Konfigurierbare Ausschlüsse im Backend verhindern Trennung in UI-, Code- oder Assistive-Text-Bereichen.

![Screenshot](https://raw.githubusercontent.com/FriendsOfREDAXO/hyphenator/assets/hyphenator_02.png)

## Verwendung

Code-Beispiele zur Verwendung innerhalb eines Modul-Outputs:

```php
use FriendsOfRedaxo\Hyphenator\Hyphenator;

// Text (aktuelle Sprache):
echo Hyphenator::hyphenate('REX_VALUE[id=1]');

// Text (andere Sprache):
echo Hyphenator::hyphenate('REX_VALUE[id=1]', 'en');

// Markup:
echo Hyphenator::hyphenate('REX_VALUE[id=1 output=html]');

// Explizit HTML-Modus:
echo Hyphenator::hyphenateHtml('REX_VALUE[id=1 output=html]');

// Explizit Text-Modus:
echo Hyphenator::hyphenateText('REX_VALUE[id=1]');

// Textile im MarkItUp Editor:
$textile = rex_markitup::parseOutput('textile', 'REX_VALUE[id=1 output="html"]');
echo Hyphenator::hyphenate($textile);
```

## Migration von Vorversionen

Ab Version 2 ist die primäre API namespaced:

```php
use FriendsOfRedaxo\Hyphenator\Hyphenator;
```

<!-- Legacy-Hinweis intern: Die alte Schreibweise ohne Namespace bleibt aktuell über die Bridge class.hyphenator.php kompatibel. -->

## Locale-Erkennung im Template

Die Sprache wird in dieser Reihenfolge bestimmt:

1. Expliziter Parameter beim Aufruf (empfohlen, wenn eindeutig bekannt)
2. Aktuelle REDAXO-Sprache (`rex_clang::getCurrent()->getCode()`), wenn kein Parameter gesetzt ist
3. Im HTML-Modus pro Element über `lang`-Attribute (überschreibt Sprache im jeweiligen Subtree)

Beispiele:

```php
use FriendsOfRedaxo\Hyphenator\Hyphenator;

// 1) Explizit pro Aufruf
echo Hyphenator::hyphenate($text, 'de-DE');

// 2) Automatisch aus aktueller REDAXO-Sprache
echo Hyphenator::hyphenate($text);

// 3) Gemischte Sprache in HTML über lang-Attribute
$html = '<p lang="de-DE">Das ist ein deutscher Absatz.</p>'
	. '<p lang="en-GB">This paragraph is in English.</p>';
echo Hyphenator::hyphenateHtml($html);
```

Hinweise:

- Für gemischte Inhalte in einem HTML-Block immer `lang` an den betroffenen Elementen setzen.
- Für reine Textstrings ohne HTML ist der Sprachparameter der robusteste Weg.

## Trenn-Symbol / Soft-Hyphen

Standardmäßig verwendet das Addon das unsichtbare Soft-Hyphen `&shy;` als Trennzeichen.

- Wenn das Feld `Trenn-Symbol` in der Konfiguration leer bleibt, wird automatisch dieses Standardzeichen verwendet.
- Für eine besser sichtbare Eingabe im Backend dürfen dort auch `&shy;`, `&#173;` oder `&#xAD;` eingetragen werden. Diese Werte werden intern automatisch auf das echte Soft-Hyphen-Zeichen normalisiert.
- Wer bewusst ein sichtbares Trennzeichen möchte, kann stattdessen z. B. `-` speichern.

### Bereiche von der Trennung ausschließen

- Globale Ausschlüsse über die Konfiguration (`Ausgeschlossene HTML-Tags`, `Ausgeschlossene CSS-Klassen`)
- Freie CSS-Selektoren über `Ausgeschlossene CSS-Selektoren` (ein Selektor pro Zeile)
- Pro Element über Attribut `data-hyphenator-ignore`
- Alternativ pro Element über `data-hyphenator="off"`
- Standardmäßig werden u. a. `code`, `pre`, `script`, `style`, `textarea`, `svg` und `math` ausgelassen

Beispiele für Selektoren:

- `#main-nav`
- `.teaser a`
- `article p:not(.allow-hyphen)`
- `aside :is(a, button)`

## Neue Features in 2.x

- Namespaced API: `FriendsOfRedaxo\Hyphenator\Hyphenator`
- HTML5-DOM-Verarbeitung über `Dom\HTMLDocument` (PHP 8.4+)
- Sprachableitung im DOM über `lang`-Attribute je Element/Subtree
- Locale-Normalisierung über `ext-intl` (`Locale::canonicalize`, `Locale::parseLocale`)
- Freie CSS-Selektor-Ausschlüsse inkl. ID-, Klassen- und Pseudo-Selektoren
- Erweiterte Ignore-Regeln pro Element über `data-hyphenator-ignore` und `data-hyphenator="off"`

## Technische Änderungen

- Backend-Konfiguration gehärtet: CSRF-Token-Prüfung beim Speichern
- Konfigurationswerte werden serverseitig begrenzt (`leftMin` 1-10, `rightMin` 1-10, `wordMin` 2-50)
- Legacy-Klasse `class.hyphenator.php` bleibt als Bridge auf die Namespaced-Class erhalten
- Veraltete Hilfeseite `help.php` entfernt

## Anforderungen

- PHP >= 8.4
- ext-intl
- REDAXO >= 5.19.0

## Installation

Dieses AddOn enthält keine vendorten Abhängigkeiten mehr. Die Bibliotheken werden per Composer installiert:

```bash
cd redaxo/src/addons/hyphenator
composer install --no-dev --optimize-autoloader
```

## Hinweis zum Upstream-Stand

Die Datei CHANGELOG im Upstream-Paket kann veraltet wirken. Für den tatsächlichen Stand sind die Composer-Metadaten und die installierte Paketversion maßgeblich.
