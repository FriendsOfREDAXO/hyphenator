<?php
	$code = "";
	$code .= "<?php".PHP_EOL;
	$code .= "	//Plaintext (aktuelle Sprache):".PHP_EOL;
	$code .= "	echo hyphenator::hyphenate(REX_VALUE[id=1]);".PHP_EOL;
	$code .= PHP_EOL;
	$code .= "	//Markup (aktuelle Sprache):".PHP_EOL;
	$code .= "	echo hyphenator::hyphenate(REX_VALUE[id=1 output=html]);".PHP_EOL;
	$code .= PHP_EOL;
	$code .= "	//Plaintext (andere Sprache):".PHP_EOL;
	$code .= "	echo hyphenator::hyphenate(REX_VALUE[id=1], 'en');".PHP_EOL;
	$code .= PHP_EOL;
	$code .= "	//Markup (andere Sprache):".PHP_EOL;
	$code .= "	echo hyphenator::hyphenate(REX_VALUE[id=1 output=html], 'en');".PHP_EOL;
	$code .= "?>";
	
	$fragment = new rex_fragment();
	$fragment->setVar('class', 'info', false);
	$fragment->setVar('title', 'Beispiel: Module Output', false); //translate
	$fragment->setVar('body', rex_string::highlight($code), false);
	echo $fragment->parse('core/page/section.php');
?>