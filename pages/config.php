<?php

    $content = '';

    if (rex_post('config-submit', 'boolean')) {
        $this->setConfig(rex_post('config', [
            ['hyphen', 'string'],
            ['leftMin', 'int'],
            ['rightMin', 'int'],
            ['wordMin', 'int'],
        ]));
        $content .= rex_view::info($this->i18n('config_saved'));
    }

    $content .= '<div class="rex-form">';
    $content .= '  <form action="'.rex_url::currentBackendPage().'" method="post">';
    $content .= '    <fieldset>';

    $formElements = [];

    //Start - hyphen
        $n = [];
        $n['label'] = '<label for="hyphenator-config-hyphen">'.$this->i18n('config_hyphen').'</label>';
        $n['field'] = '<input type="text" id="hyphenator-config-hyphen" name="config[hyphen]" value="'.htmlspecialchars($this->getConfig('hyphen')).'">';
        $n['note'] = $this->i18n('config_hyphen_description');
        $formElements[] = $n;
    //End - hyphen

    //Start - leftMin
        $n = [];
        $n['label'] = '<label for="hyphenator-config-leftMin">'.$this->i18n('config_leftMin').'</label>';
        $n['field'] = '<input type="text" id="hyphenator-config-leftMin" name="config[leftMin]" value="'.$this->getConfig('leftMin').'">';
        $n['note'] = $this->i18n('config_leftMin_description');
        $formElements[] = $n;
    //End - leftMin

    //Start - rightMin
        $n = [];
        $n['label'] = '<label for="hyphenator-config-rightMin">'.$this->i18n('config_rightMin').'</label>';
        $n['field'] = '<input type="text" id="hyphenator-config-rightMin" name="config[rightMin]" value="'.$this->getConfig('rightMin').'">';
        $n['note'] = $this->i18n('config_rightMin_description');
        $formElements[] = $n;
    //End - rightMin

    //Start - wordMin
        $n = [];
        $n['label'] = '<label for="hyphenator-config-wordMin">'.$this->i18n('config_wordMin').'</label>';
        $n['field'] = '<input type="text" id="hyphenator-config-wordMin" name="config[wordMin]" value="'.$this->getConfig('wordMin').'">';
        $n['note'] = $this->i18n('config_wordMin_description');
        $formElements[] = $n;
    //End - wordMin

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $content .= $fragment->parse('core/form/form.php');

    $content .= '    </fieldset>';

    $content .= '    <fieldset class="rex-form-action">';

    $formElements = [];

    $n = [];
    $n['field'] = '<input type="submit" name="config-submit" value="'.$this->i18n('config_action_save').'" '.rex::getAccesskey($this->i18n('config_action_save'), 'save').'>';
    $formElements[] = $n;

    $fragment = new rex_fragment();
    $fragment->setVar('elements', $formElements, false);
    $content .= $fragment->parse('core/form/submit.php');

    $content .= '    </fieldset>';
    $content .= '  </form>';
    $content .= '</div>';

    $fragment = new rex_fragment();
    $fragment->setVar('class', 'edit');
    $fragment->setVar('title', $this->i18n('config'));
    $fragment->setVar('body', $content, false);
    echo $fragment->parse('core/page/section.php');
