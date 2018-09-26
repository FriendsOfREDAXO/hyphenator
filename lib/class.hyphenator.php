<?php
	class hyphenator {
		private static $hyphenators = array();
				
		public static function hyphenate($string, $language = '') {
			//Start - save html-tags
				preg_match_all('/<[^<]*>/', $string, $matches, PREG_SET_ORDER);
				foreach ($matches as $index => $match) {
					$string = preg_replace('|'.preg_quote($match[0],'|').'|', '###'.$index.'###', $string, 1);
				}
			//End - save html-attributes
			
			if ($language == '') {
				$language = rex_clang::getCurrent()->getCode();
			}
			
			$string = self::getHyphenator($language)->hyphenate($string);
			
			//Start - restore html-attributes
				foreach ($matches as $index => $match) {
					$string = preg_replace('|###'.$index.'###|', $match[0], $string, 1);
				}
			//End - restore html-attributes
			
			
			
			return $string;
		}
		
		private static function getHyphenator($language) {
			if (!isset(self::$hyphenators[$language])) {
				$addon = rex_addon::get(__CLASS__);
				
				$h = \Org\Heigl\Hyphenator\Hyphenator::factory(null, self::getLocale($language));
				$o = $h->getOptions();
				
				if (!empty($addon->getConfig('hyphen'))) {
					$o->setHyphen($addon->getConfig('hyphen'));
				} else {
					$o->setHyphen('&shy;');
				}
				
				//Bugfix for safari
				//see: https://blog.decaf.de/2016/03/04/705665289569099777/
				//if (stripos($_SERVER ['HTTP_USER_AGENT'], 'safari') !== false && stripos($_SERVER ['HTTP_USER_AGENT'], 'chrome') == false && $o->getHyphen() == '&shy;') {
				//	$o->setHyphen('<i></i>&shy;');
				//}
				
				if (!empty($addon->getConfig('leftMin'))) {
					$o->setLeftMin($addon->getConfig('leftMin'));
				} else {
					$o->setLeftMin(2);
				}
				
				if (!empty($addon->getConfig('rightMin'))) {
					$o->setRightMin($addon->getConfig('rightMin'));
				} else {
					$o->setRightMin(2);
				}
				
				if (!empty($addon->getConfig('wordMin'))) {
					$o->setWordMin($addon->getConfig('wordMin'));
				} else {
					$o->setWordMin(6);
				}
				
				$h->setOptions($o);
				self::$hyphenators[$language] = $h;
			}
			
			return self::$hyphenators[$language];
		}
		
		private static function getLocale($language) {
			switch ($language) {
				case 'de':
					$language = 'de_DE';
				break;
				case 'en':
					$language = 'en_GB';
				break;
			}
			
			return $language;
		}
	}
?>