Hyphenator
==========

Addon für REDAXO 5: Ermöglicht Silbentrennung auf Basis von PHP.

![Screenshot](https://raw.githubusercontent.com/FriendsOfREDAXO/hyphenator/assets/hyphenator_01.png)

## Funktion

Das Addon verwendet die [Hyphenator](https://github.com/heiglandreas/Org_Heigl_Hyphenator)-Bibliothek, um eine serverseitige Silbentrennung — hier also PHP und nicht JavaScript, worauf andere Silbentrenner häufig basieren — zu ermöglichen. Diese nutzt [verschiedene Algorithmen](http://orgheiglhyphenator.readthedocs.io/en/latest/hyphenator/), um Stellen innerhalb von Wörten zu finden, an denen getrennt werden kann. Das Addon fügt an diesen Stellen ein Trennzeichen ein, das üblicherweise `&shy;` ist, jedoch innerhalb der Addon-Konfiguration frei definiert werden kann.

![Screenshot](https://raw.githubusercontent.com/FriendsOfREDAXO/hyphenator/assets/hyphenator_02.png)

## Verwendung

Code-Beispiele zur Verwendung innerhalb eines Modul-Outputs:

```php
// Text (aktuelle Sprache):
echo hyphenator::hyphenate('REX_VALUE[id=1]');

// Text (andere Sprache):
echo hyphenator::hyphenate('REX_VALUE[id=1]', 'en');

// Markup:
echo hyphenator::hyphenate('REX_VALUE[id=1 output=html]');

// Textile im MarkItUp Editor:
$textile = rex_markitup::parseOutput('textile', 'REX_VALUE[id=1 output="html"]');
echo hyphenator::hyphenate($textile);
```

## Anmerkungen

* WebKit (Safari, Opera) hat Probleme mit `&shy;` in manchen WOFF-Webfonts. Es tauchen dann an der Stelle des Umbruchs eigenartige Zeichen auf (siehe [Bugtracker-Issue](https://bugs.webkit.org/show_bug.cgi?id=156167)).  
Als Workaround kann `<i></i>&shy;` als Trennzeichen verwendet werden, was das Addon in der Standardkonfiguration bereits eigenständig übernimmt.
