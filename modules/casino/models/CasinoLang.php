<?php

/**
 * Casino texts in the language of the visitor (modules/casino/language/<folder>/casino_lang.php).
 * The language is the framework's own: ?language=<folder> switches it and keeps it in the session.
 */
class CasinoLang
{
    /** Language folder => [label, locale for numbers, html lang]. Only the folders that have a casino_lang.php are offered. */
    public const LANGUAGES = [
        'dutch' => ['Nederlands', 'nl-NL', 'nl'],
        'english' => ['English', 'en-GB', 'en'],
        'german' => ['Deutsch', 'de-DE', 'de'],
        'chinese' => ['中文', 'zh-CN', 'zh-CN'],
    ];

    /** Text for casino_<key>; {name} placeholders are replaced by $vars. Falls back to the key itself. */
    public static function t(string $key, array $vars = []): string
    {
        self::ensureLoaded();
        $text = lang('casino_' . $key);
        if ($text === '') {
            $text = $key;
        }
        foreach ($vars as $name => $value) {
            $text = str_replace('{' . $name . '}', (string)$value, $text);
        }
        return $text;
    }

    /** A language without casino texts (e.g. French) gets the English ones. */
    private static function ensureLoaded(): void
    {
        if (!isset(CILang()->language['casino_title'])) {
            CILang()->load('casino', 'english', false, true, MODULESPATH . 'casino' . DIRECTORY_SEPARATOR);
        }
    }

    public static function current(): string
    {
        $lang = LanguageModel::getLanguage();
        return isset(self::LANGUAGES[$lang]) ? $lang : 'english';
    }

    public static function locale(): string
    {
        return self::LANGUAGES[self::current()][1];
    }

    public static function htmlLang(): string
    {
        return self::LANGUAGES[self::current()][2];
    }

    /** The casino_js_* texts for the game scripts (key without the prefix). */
    public static function js(): array
    {
        self::ensureLoaded();
        $result = [];
        foreach (array_keys(CILang()->language) as $key) {
            if (str_starts_with($key, 'casino_js_')) {
                $short = substr($key, strlen('casino_js_'));
                $result[$short] = self::t('js_' . $short);
            }
        }
        return $result;
    }

    /** Dropdown with the languages (Bootstrap), empty when there is only one. */
    public static function switcherHtml(): string
    {
        $list = self::switcher();
        if (count($list) < 2) {
            return '';
        }
        $label = '';
        $items = '';
        foreach ($list as $l) {
            $label = $l['active'] ? $l['label'] : $label;
            $items .= '<li><a class="dropdown-item' . ($l['active'] ? ' active' : '') . '" href="' . htmlspecialchars($l['url']) . '">' . htmlspecialchars($l['label']) . '</a></li>';
        }
        return '<div class="dropdown cs-lang"><button class="btn btn-sm btn-outline-light dropdown-toggle" type="button" data-bs-toggle="dropdown" title="'
            . htmlspecialchars(self::t('language')) . '"><i class="fas fa-globe"></i> ' . htmlspecialchars($label) . '</button>'
            . '<ul class="dropdown-menu dropdown-menu-end">' . $items . '</ul></div>';
    }

    /** Links to switch the language on the current page: [[folder, label, url, active], ...] */
    public static function switcher(): array
    {
        $current = self::current();
        $get = CIInput()->get() ?: [];
        $base = current_url();
        $list = [];
        foreach (self::LANGUAGES as $folder => $info) {
            if (!is_file(MODULESPATH . 'casino' . DIRECTORY_SEPARATOR . 'language' . DIRECTORY_SEPARATOR . $folder . DIRECTORY_SEPARATOR . 'casino_lang.php')) {
                continue;
            }
            $query = http_build_query(array_merge($get, ['language' => $folder]));
            $list[] = ['folder' => $folder, 'label' => $info[0], 'url' => $base . '?' . $query, 'active' => $folder === $current];
        }
        return $list;
    }
}
