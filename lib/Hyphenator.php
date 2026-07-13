<?php

namespace FriendsOfRedaxo\Hyphenator;

class Hyphenator
{
    private const EXCLUDE_RUNTIME_ATTRIBUTE = 'data-hyphenator-ignore-runtime';
    private const DEFAULT_HYPHEN = '&shy;';
    private const DEFAULT_LEFT_MIN = 2;
    private const DEFAULT_RIGHT_MIN = 2;
    private const DEFAULT_WORD_MIN = 6;

    private const DEFAULT_EXCLUDED_TAGS = [
        'code',
        'kbd',
        'math',
        'noscript',
        'option',
        'pre',
        'samp',
        'script',
        'style',
        'svg',
        'textarea',
    ];

    private const DEFAULT_EXCLUDED_CLASSES = [
        'hyphenator-ignore',
        'no-hyphen',
        'notranslate',
        'sr-only',
    ];

    private static array $hyphenators = [];
    private static ?array $config = null;

    public static function hyphenate(string $string, string $language = ''): string
    {
        if ('' === $string) {
            return '';
        }

        $language = self::normalizeLanguage($language);

        if (self::looksLikeHtml($string)) {
            return self::hyphenateHtml($string, $language);
        }

        return self::hyphenateText($string, $language);
    }

    public static function hyphenateText(string $text, string $language = ''): string
    {
        if ('' === $text) {
            return '';
        }

        $config = self::getConfig();
        $hyphenator = self::getHyphenator(self::normalizeLanguage($language));
        $result = preg_replace_callback(
            '/\p{L}[\p{L}\p{Mn}\p{Pc}\x{2019}\']*/u',
            static function (array $match) use ($hyphenator, $config): string {
                if (self::shouldExcludeWord($match[0], $config['excludeWordsLookup'])) {
                    return $match[0];
                }

                return $hyphenator->hyphenate($match[0]);
            },
            $text,
        );

        return is_string($result) ? $result : $text;
    }

    public static function hyphenateHtml(string $html, string $language = ''): string
    {
        if ('' === $html) {
            return '';
        }

        if (!class_exists(\Dom\HTMLDocument::class)) {
            throw new \RuntimeException('hyphenator requires PHP 8.4+ with Dom\\HTMLDocument support.');
        }

        $defaultLanguage = self::normalizeLanguage($language);
        $document = \Dom\HTMLDocument::createFromString('<!doctype html><html><body><div id="hyphenator-root">' . $html . '</div></body></html>');
        $root = $document->getElementById('hyphenator-root');

        if (!$root instanceof \Dom\Element) {
            return $html;
        }

        $config = self::getConfig();
        self::applyExcludedSelectors($root, $config['excludeSelectors']);
        self::hyphenateNode(
            $root,
            $config['excludeTags'],
            $config['excludeClasses'],
            $config['excludeWordsLookup'],
            $defaultLanguage,
            $defaultLanguage,
        );

        return $root->innerHTML;
    }

    private static function hyphenateNode(
        \Dom\Node $node,
        array $excludedTags,
        array $excludedClasses,
        array $excludedWordsLookup,
        string $defaultLanguage,
        string $currentLanguage,
        bool $inExcludedContext = false
    ): void {
        $isExcluded = $inExcludedContext;
        $activeLanguage = $currentLanguage;

        if ($node instanceof \Dom\Element) {
            $isExcluded = $isExcluded || self::isExcludedElement($node, $excludedTags, $excludedClasses);
            $activeLanguage = self::resolveElementLanguage($node, $defaultLanguage, $currentLanguage);
        }

        if ($node instanceof \Dom\Text) {
            if (!$isExcluded) {
                $node->textContent = self::hyphenateTextNode($node->textContent, self::getHyphenator($activeLanguage), $excludedWordsLookup);
            }

            return;
        }

        $children = $node->childNodes;
        for ($i = 0; $i < $children->length; ++$i) {
            $child = $children->item($i);
            if ($child instanceof \Dom\Node) {
                self::hyphenateNode(
                    $child,
                    $excludedTags,
                    $excludedClasses,
                    $excludedWordsLookup,
                    $defaultLanguage,
                    $activeLanguage,
                    $isExcluded,
                );
            }
        }
    }

    private static function hyphenateTextNode(string $text, \Org\Heigl\Hyphenator\Hyphenator $hyphenator, array $excludedWordsLookup): string
    {
        if ('' === trim($text)) {
            return $text;
        }

        $result = preg_replace_callback(
            '/\p{L}[\p{L}\p{Mn}\p{Pc}\x{2019}\']*/u',
            static function (array $match) use ($hyphenator, $excludedWordsLookup): string {
                if (self::shouldExcludeWord($match[0], $excludedWordsLookup)) {
                    return $match[0];
                }

                return $hyphenator->hyphenate($match[0]);
            },
            $text,
        );

        return is_string($result) ? $result : $text;
    }

    private static function isExcludedElement(\Dom\Element $element, array $excludedTags, array $excludedClasses): bool
    {
        if ($element->hasAttribute(self::EXCLUDE_RUNTIME_ATTRIBUTE)) {
            return true;
        }

        $tagName = strtolower($element->localName);
        if (in_array($tagName, $excludedTags, true)) {
            return true;
        }

        if ($element->hasAttribute('data-hyphenator-ignore')) {
            return true;
        }

        $hyphenatorState = strtolower(trim(self::getElementAttribute($element, 'data-hyphenator')));
        if ('off' === $hyphenatorState || 'false' === $hyphenatorState) {
            return true;
        }

        if ('true' === strtolower(self::getElementAttribute($element, 'aria-hidden'))) {
            return true;
        }

        $classes = trim(self::getElementAttribute($element, 'class'));
        if ('' === $classes) {
            return false;
        }

        foreach (preg_split('/\s+/', $classes) as $className) {
            if (in_array(strtolower((string) $className), $excludedClasses, true)) {
                return true;
            }
        }

        return false;
    }

    private static function getHyphenator(string $language): \Org\Heigl\Hyphenator\Hyphenator
    {
        if (!class_exists(\Org\Heigl\Hyphenator\Hyphenator::class)) {
            throw new \RuntimeException('Missing dependency org_heigl/hyphenator. Run "composer install" in redaxo/src/addons/hyphenator.');
        }

        $config = self::getConfig();
        $cacheKey = $language . '|' . md5((string) json_encode([
            'hyphen' => $config['hyphen'],
            'leftMin' => $config['leftMin'],
            'rightMin' => $config['rightMin'],
            'wordMin' => $config['wordMin'],
        ]));

        if (!isset(self::$hyphenators[$cacheKey])) {
            $hyphenator = \Org\Heigl\Hyphenator\Hyphenator::factory(null, self::getLocale($language));
            $options = $hyphenator->getOptions();

            $options->setHyphen($config['hyphen']);
            $options->setLeftMin($config['leftMin']);
            $options->setRightMin($config['rightMin']);
            $options->setWordMin($config['wordMin']);

            $hyphenator->setOptions($options);
            self::$hyphenators[$cacheKey] = $hyphenator;
        }

        return self::$hyphenators[$cacheKey];
    }

    private static function getConfig(): array
    {
        if (null !== self::$config) {
            return self::$config;
        }

        $addon = \rex_addon::get('hyphenator');
        $rawConfig = $addon->getConfig();

        $hyphen = trim((string) ($rawConfig['hyphen'] ?? ''));
        if ('' === $hyphen) {
            $hyphen = self::DEFAULT_HYPHEN;
        }

        self::$config = [
            'hyphen' => $hyphen,
            'leftMin' => self::limitInt($rawConfig['leftMin'] ?? null, self::DEFAULT_LEFT_MIN, 1, 10),
            'rightMin' => self::limitInt($rawConfig['rightMin'] ?? null, self::DEFAULT_RIGHT_MIN, 1, 10),
            'wordMin' => self::limitInt($rawConfig['wordMin'] ?? null, self::DEFAULT_WORD_MIN, 2, 50),
            'excludeTags' => self::normalizeList(
                (string) ($rawConfig['excludeTags'] ?? ''),
                self::DEFAULT_EXCLUDED_TAGS,
            ),
            'excludeClasses' => self::normalizeList(
                (string) ($rawConfig['excludeClasses'] ?? ''),
                self::DEFAULT_EXCLUDED_CLASSES,
            ),
            'excludeSelectors' => self::normalizeSelectors((string) ($rawConfig['excludeSelectors'] ?? '')),
            'excludeWordsLookup' => self::buildWordLookup((string) ($rawConfig['excludeWords'] ?? '')),
        ];

        return self::$config;
    }

    private static function normalizeList(string $value, array $defaults): array
    {
        if ('' === trim($value)) {
            return $defaults;
        }

        $parts = preg_split('/\s*,\s*/', strtolower($value));
        if (!is_array($parts)) {
            return $defaults;
        }

        $parts = array_values(array_filter(array_unique(array_map('trim', $parts)), static fn (string $entry): bool => '' !== $entry));

        return [] !== $parts ? $parts : $defaults;
    }

    private static function normalizeSelectors(string $value): array
    {
        if ('' === trim($value)) {
            return [];
        }

        $lines = preg_split('/\r\n|\r|\n|;/', $value);
        if (!is_array($lines)) {
            return [];
        }

        $selectors = [];
        foreach ($lines as $line) {
            $selector = trim((string) $line);
            if ('' !== $selector) {
                $selectors[] = $selector;
            }
        }

        return array_values(array_unique($selectors));
    }

    private static function buildWordLookup(string $value): array
    {
        if ('' === trim($value)) {
            return [];
        }

        $parts = preg_split('/\r\n|\r|\n|,|;/', $value);
        if (!is_array($parts)) {
            return [];
        }

        $lookup = [];
        foreach ($parts as $entry) {
            $normalized = self::normalizeWord((string) $entry);
            if ('' !== $normalized) {
                $lookup[$normalized] = true;
            }
        }

        return $lookup;
    }

    private static function shouldExcludeWord(string $word, array $lookup): bool
    {
        if ([] === $lookup) {
            return false;
        }

        return isset($lookup[self::normalizeWord($word)]);
    }

    private static function normalizeWord(string $word): string
    {
        $word = trim($word);
        if ('' === $word) {
            return '';
        }

        if (function_exists('mb_strtolower')) {
            return mb_strtolower($word, 'UTF-8');
        }

        return strtolower($word);
    }

    private static function applyExcludedSelectors(\Dom\Element $root, array $selectors): void
    {
        if ([] === $selectors || !method_exists($root, 'querySelectorAll')) {
            return;
        }

        foreach ($selectors as $selector) {
            try {
                $nodes = $root->querySelectorAll($selector);
            } catch (\Throwable) {
                continue;
            }

            if (!$nodes instanceof \Dom\NodeList) {
                continue;
            }

            for ($i = 0; $i < $nodes->length; ++$i) {
                $node = $nodes->item($i);
                if ($node instanceof \Dom\Element) {
                    $node->setAttribute(self::EXCLUDE_RUNTIME_ATTRIBUTE, '1');
                }
            }
        }
    }

    private static function limitInt(mixed $value, int $fallback, int $min, int $max): int
    {
        if (!is_scalar($value) || '' === (string) $value) {
            return $fallback;
        }

        $number = (int) $value;
        if ($number < $min) {
            return $min;
        }

        if ($number > $max) {
            return $max;
        }

        return $number;
    }

    private static function looksLikeHtml(string $value): bool
    {
        return preg_match('/<[^>]+>/', $value) === 1;
    }

    private static function normalizeLanguage(string $language): string
    {
        if ('' === trim($language)) {
            $clangCode = \rex_clang::getCurrent()->getCode();
            $language = is_string($clangCode) ? $clangCode : '';
        }

        if ('' === trim($language)) {
            $language = 'en';
        }

        $normalized = str_replace('-', '_', trim($language));

        if (class_exists(\Locale::class)) {
            $canonical = \Locale::canonicalize($normalized);
            if (is_string($canonical) && '' !== $canonical) {
                $normalized = str_replace('-', '_', $canonical);
            }
        }

        return strtolower($normalized);
    }

    private static function resolveElementLanguage(\Dom\Element $element, string $defaultLanguage, string $currentLanguage): string
    {
        $lang = trim(self::getElementAttribute($element, 'lang'));
        if ('' === $lang) {
            return $currentLanguage;
        }

        $normalized = self::normalizeLanguage($lang);

        return '' !== $normalized ? $normalized : $defaultLanguage;
    }

    private static function getLocale(string $language): string
    {
        if (!class_exists(\Locale::class)) {
            throw new \RuntimeException('The intl extension is required.');
        }

        $mapping = [
            'de' => 'de_DE',
            'en' => 'en_GB',
            'es' => 'es_ES',
            'pt' => 'pt_BR',
            'sv' => 'sv_SE',
        ];

        $canonical = \Locale::canonicalize(str_replace('_', '-', $language));
        if (is_string($canonical) && '' !== $canonical) {
            $parts = \Locale::parseLocale($canonical);
            $lang = isset($parts['language']) ? strtolower((string) $parts['language']) : '';
            $region = isset($parts['region']) ? strtoupper((string) $parts['region']) : '';

            if ('' !== $lang && '' !== $region) {
                return $lang . '_' . $region;
            }

            if ('' !== $lang && isset($mapping[$lang])) {
                return $mapping[$lang];
            }
        }

        if (preg_match('/^[a-z]{2}_[a-z]{2}$/', $language) === 1) {
            return substr($language, 0, 2) . '_' . strtoupper(substr($language, 3, 2));
        }

        return $mapping[substr($language, 0, 2)] ?? 'en_GB';
    }

    private static function getElementAttribute(\Dom\Element $element, string $attribute): string
    {
        $value = $element->getAttribute($attribute);

        return is_string($value) ? $value : '';
    }
}
