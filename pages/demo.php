<?php

use FriendsOfRedaxo\Hyphenator\Hyphenator;

/** @var rex_addon $this */

$clangCode = rex_clang::getCurrent()->getCode();
$requestedLanguage = is_string($clangCode) && '' !== trim($clangCode) ? $clangCode : 'de';

$sourceHtml = <<<'HTML'
<div id="main-nav">
    Die Schifffahrtsgesellschaftskapitänsvereinigung und die Kraftfahrzeughaftpflichtversicherung bleiben hier als ungetrennter Kontrollbereich.
</div>

<p class="teaser">
    Die Rechtsschutzversicherungsgesellschaften und die Donaudampfschifffahrtsgesellschaftskapitänswitwe diskutieren über die
    Arbeitsunfähigkeitsbescheinigungsanforderungen in der Verwaltungsmodernisierung.
</p>

<p>
    Ein klassischer Beispieltext mit langen Wörtern wie Rindfleischetikettierungsüberwachungsaufgabenübertragungsgesetz,
    Hochgeschwindigkeitsdatenübertragungsprotokoll und Elektrizitätswirtschaftsorganisationsgesetz.
</p>

<p>
    Internationalization, incomprehensibilities, electroencephalographically and counterrevolutionaries are used as long-word tests.
</p>

<p lang="en-GB">
    Dieser Absatz hat ein festes lang="en-GB" und wird daher unabhängig von der globalen Sprache immer mit englischem Trennmuster verarbeitet.
</p>

<p data-hyphenator="off">
    Dieser Absatz bleibt immer unverändert, weil data-hyphenator="off" gesetzt ist.
</p>
HTML;

$resultHtml = Hyphenator::hyphenateHtml($sourceHtml, $requestedLanguage);
$normalizedResultHtml = str_replace(
    ['&amp;shy;', '&shy;', '&#173;', '&#xAD;', '&#xad;'],
    "\u{00AD}",
    $resultHtml,
);
$debugMarker = '<span class="hyphenator-demo-shy">(-)</span>';
$debugSourceHtml = str_replace("\u{00AD}", $debugMarker, rex_escape($sourceHtml));
$debugResultHtml = str_replace("\u{00AD}", $debugMarker, rex_escape($normalizedResultHtml));

echo rex_view::info('Globale Demo-Sprache (aus aktueller REDAXO-Sprache): ' . rex_escape($requestedLanguage));

echo rex_view::info($this->i18n('demo_description'));
echo rex_view::info($this->i18n('demo_hint'));
echo rex_view::info('Die globale Sprache wirkt auf alle Bereiche ohne eigenes lang-Attribut. Elemente mit lang="..." behalten ihr eigenes Trennmuster.');

$demoStyles = '<style>
.hyphenator-demo-resize {
    resize: horizontal;
    overflow: auto;
    min-width: 240px;
    max-width: 100%;
    width: 460px;
    border: 1px solid #d8dce1;
    border-radius: 4px;
    background: #fff;
    padding: 12px;
}

.hyphenator-demo-preview {
    line-height: 1.55;
    word-break: normal;
    overflow-wrap: normal;
}

.hyphenator-demo-subtitle {
    margin-top: 16px;
    margin-bottom: 8px;
}

.hyphenator-demo-shy {
    color: #7a8795;
    background-color: #fff3bf;
    border-radius: 3px;
    font-weight: 600;
    padding: 0 2px;
}
</style>';

echo $demoStyles;

$sourceCard = '<h4>' . rex_escape($this->i18n('demo_source')) . '</h4>'
    . '<h5 class="hyphenator-demo-subtitle">' . rex_escape($this->i18n('demo_preview_rendered')) . '</h5>'
    . '<div class="hyphenator-demo-resize">'
    . '<div class="hyphenator-demo-preview">' . $sourceHtml . '</div>'
    . '</div>';

$resultCard = '<h4>' . rex_escape($this->i18n('demo_result')) . '</h4>'
    . '<h5 class="hyphenator-demo-subtitle">' . rex_escape($this->i18n('demo_preview_rendered')) . '</h5>'
    . '<div class="hyphenator-demo-resize">'
    . '<div class="hyphenator-demo-preview">' . $normalizedResultHtml . '</div>'
    . '</div>';

$technicalCard = '<h4>' . rex_escape($this->i18n('demo_preview_markup')) . '</h4>'
    . '<div class="row">'
    . '<div class="col-md-6">'
    . '<h5 class="hyphenator-demo-subtitle">' . rex_escape($this->i18n('demo_source')) . '</h5>'
    . '<pre style="white-space: pre-wrap;">' . $debugSourceHtml . '</pre>'
    . '</div>'
    . '<div class="col-md-6">'
    . '<h5 class="hyphenator-demo-subtitle">' . rex_escape($this->i18n('demo_result')) . '</h5>'
    . '<pre style="white-space: pre-wrap;">' . $debugResultHtml . '</pre>'
    . '</div>'
    . '</div>';

$body = '<div class="row">'
    . '<div class="col-md-6">' . $sourceCard . '</div>'
    . '<div class="col-md-6">' . $resultCard . '</div>'
    . '</div>'
    . '<div class="row" style="margin-top: 20px;">'
    . '<div class="col-md-12">' . $technicalCard . '</div>'
    . '</div>';

$fragment = new rex_fragment();
$fragment->setVar('class', 'default');
$fragment->setVar('title', $this->i18n('demo_result'));
$fragment->setVar('body', $body, false);
echo $fragment->parse('core/page/section.php');
