<?php

/** @var rex_addon $this */

$csrfToken = rex_csrf_token::factory('hyphenator_config');

if (rex_post('config-submit', 'boolean')) {
    if ($csrfToken->isValid()) {
        $postedConfig = rex_post('config', [
            ['hyphen', 'string'],
            ['leftMin', 'int'],
            ['rightMin', 'int'],
            ['wordMin', 'int'],
            ['excludeTags', 'string'],
            ['excludeClasses', 'string'],
            ['excludeSelectors', 'string'],
        ]);

        $this->setConfig([
            'hyphen' => trim((string) ($postedConfig['hyphen'] ?? '')),
            'leftMin' => max(1, min(10, (int) ($postedConfig['leftMin'] ?? 2))),
            'rightMin' => max(1, min(10, (int) ($postedConfig['rightMin'] ?? 2))),
            'wordMin' => max(2, min(50, (int) ($postedConfig['wordMin'] ?? 6))),
            'excludeTags' => trim((string) ($postedConfig['excludeTags'] ?? '')),
            'excludeClasses' => trim((string) ($postedConfig['excludeClasses'] ?? '')),
            'excludeSelectors' => trim((string) ($postedConfig['excludeSelectors'] ?? '')),
        ]);

        echo rex_view::success($this->i18n('config_saved'));
    } else {
        echo rex_view::error($this->i18n('config_token_invalid'));
    }
}

$content = '<fieldset>';

$formElements = [];

//Start - hyphen
    $n = [];
    $n['label'] = '<label for="hyphenator-config-hyphen">'.$this->i18n('config_hyphen').'</label>';
    $n['field'] = '<input class="form-control" type="text" id="hyphenator-config-hyphen" name="config[hyphen]" value="'.rex_escape($this->getConfig('hyphen')).'">';
    $n['note'] = $this->i18n('config_hyphen_description');
    $formElements[] = $n;
//End - hyphen

//Start - leftMin
    $n = [];
    $n['label'] = '<label for="hyphenator-config-leftMin">'.$this->i18n('config_leftMin').'</label>';
    $n['field'] = '<input class="form-control" type="number" min="1" max="10" id="hyphenator-config-leftMin" name="config[leftMin]" value="'.rex_escape((string) $this->getConfig('leftMin', 2)).'">';
    $n['note'] = $this->i18n('config_leftMin_description');
    $formElements[] = $n;
//End - leftMin

//Start - rightMin
    $n = [];
    $n['label'] = '<label for="hyphenator-config-rightMin">'.$this->i18n('config_rightMin').'</label>';
    $n['field'] = '<input class="form-control" type="number" min="1" max="10" id="hyphenator-config-rightMin" name="config[rightMin]" value="'.rex_escape((string) $this->getConfig('rightMin', 2)).'">';
    $n['note'] = $this->i18n('config_rightMin_description');
    $formElements[] = $n;
//End - rightMin

//Start - wordMin
    $n = [];
    $n['label'] = '<label for="hyphenator-config-wordMin">'.$this->i18n('config_wordMin').'</label>';
    $n['field'] = '<input class="form-control" type="number" min="2" max="50" id="hyphenator-config-wordMin" name="config[wordMin]" value="'.rex_escape((string) $this->getConfig('wordMin', 6)).'">';
    $n['note'] = $this->i18n('config_wordMin_description');
    $formElements[] = $n;
//End - wordMin

//Start - excludeTags
    $n = [];
    $n['label'] = '<label for="hyphenator-config-excludeTags">'.$this->i18n('config_excludeTags').'</label>';
    $n['field'] = '<input class="form-control" type="text" id="hyphenator-config-excludeTags" name="config[excludeTags]" value="'.rex_escape((string) $this->getConfig('excludeTags', '')).'">';
    $n['note'] = $this->i18n('config_excludeTags_description');
    $formElements[] = $n;
//End - excludeTags

//Start - excludeClasses
    $n = [];
    $n['label'] = '<label for="hyphenator-config-excludeClasses">'.$this->i18n('config_excludeClasses').'</label>';
    $n['field'] = '<input class="form-control" type="text" id="hyphenator-config-excludeClasses" name="config[excludeClasses]" value="'.rex_escape((string) $this->getConfig('excludeClasses', '')).'">';
    $n['note'] = $this->i18n('config_excludeClasses_description');
    $formElements[] = $n;
//End - excludeClasses

//Start - excludeSelectors
    $n = [];
    $n['label'] = '<label for="hyphenator-config-excludeSelectors">'.$this->i18n('config_excludeSelectors').'</label>';
    $n['field'] = '<textarea class="form-control" rows="4" id="hyphenator-config-excludeSelectors" name="config[excludeSelectors]">'.rex_escape((string) $this->getConfig('excludeSelectors', '')).'</textarea>';
    $n['note'] = $this->i18n('config_excludeSelectors_description');
    $formElements[] = $n;
//End - excludeSelectors

$fragment = new rex_fragment();
$fragment->setVar('elements', $formElements, false);
$content .= $fragment->parse('core/form/form.php');

$formElements = [];

$n = [];
$n['field'] = '<button class="btn btn-save rex-form-aligned" type="submit" name="config-submit" value="1" ' . rex::getAccesskey($this->i18n('save'), 'save') . '>' . $this->i18n('config_action_save') . '</button>';
$formElements[] = $n;

$fragment = new rex_fragment();
$fragment->setVar('flush', true);
$fragment->setVar('elements', $formElements, false);
$buttons = $fragment->parse('core/form/submit.php');

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit');
$fragment->setVar('title', $this->i18n('config'));
$fragment->setVar('body', $content, false);
$fragment->setVar('buttons', $buttons, false);
$content = $fragment->parse('core/page/section.php');

echo '
    <form action="' . rex_url::currentBackendPage() . '" method="post">
        ' . $csrfToken->getHiddenField() . '
        ' . $content . '
    </form>';
