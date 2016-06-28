<?php
	class rex_hyphenator {
		private static $hyphenators = array();
				
		public static function hyphenate($language, $string) {
			return self::getHyphenator($language)->hyphenate($string);
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