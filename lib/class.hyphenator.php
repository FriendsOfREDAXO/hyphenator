<?php
	class hyphenator {
		private static $hyphenators = array();
				
		public static function hyphenate($string, $language = '') {
			//Start - save html-attributes
				preg_match_all('/(\S+)=["\']((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']/', $string, $matches, PREG_SET_ORDER);
				foreach ($matches as $index => $match) {
					$match[0] = str_replace(['?'], ['\?'], $match[0]);
					$string = preg_replace('|'.$match[0].'|', '###'.$index.'###', $string, 1);
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
				$h = \Org\Heigl\Hyphenator\Hyphenator::factory(null, self::getLocale($language));
				$o = $h->getOptions();
				$o->setHyphen('&shy;');
				$h->setOptions($o);
				self::$hyphenators[$language] = $h;
			}
			
			return self::$hyphenators[$language];
		}
		
		private static function getLocale($language) {
			switch ($language) {
				case 'de':
					return 'de_DE';
				case 'en':
					return 'en_US';
			}
			//throw new ErrorException('Language not supported for hyphenation', ['language' => $language]);
		}
	}
?>