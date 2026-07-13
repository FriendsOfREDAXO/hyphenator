<?php

$addon = rex_addon::get('hyphenator');

echo rex_view::title($addon->i18n('hyphenator_title'));

rex_be_controller::includeCurrentPageSubPath();
