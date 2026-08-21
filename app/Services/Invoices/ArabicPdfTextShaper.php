<?php

declare(strict_types=1);

namespace App\Services\Invoices;

use DOMDocument;
use DOMXPath;

/**
 * Shapes Arabic text for PDF engines that do not implement Arabic OpenType
 * shaping. mPDF never needs this; it is only used by the emergency fallback.
 */
final class ArabicPdfTextShaper
{
    /** @var array<string, array{string, string, string|null, string|null}> */
    private const FORMS = [
        'ء' => ['ﺀ', 'ﺀ', null, null], 'آ' => ['ﺁ', 'ﺂ', null, null],
        'أ' => ['ﺃ', 'ﺄ', null, null], 'ؤ' => ['ﺅ', 'ﺆ', null, null],
        'إ' => ['ﺇ', 'ﺈ', null, null], 'ئ' => ['ﺉ', 'ﺊ', 'ﺋ', 'ﺌ'],
        'ا' => ['ﺍ', 'ﺎ', null, null], 'ب' => ['ﺏ', 'ﺐ', 'ﺑ', 'ﺒ'],
        'ة' => ['ﺓ', 'ﺔ', null, null], 'ت' => ['ﺕ', 'ﺖ', 'ﺗ', 'ﺘ'],
        'ث' => ['ﺙ', 'ﺚ', 'ﺛ', 'ﺜ'], 'ج' => ['ﺝ', 'ﺞ', 'ﺟ', 'ﺠ'],
        'ح' => ['ﺡ', 'ﺢ', 'ﺣ', 'ﺤ'], 'خ' => ['ﺥ', 'ﺦ', 'ﺧ', 'ﺨ'],
        'د' => ['ﺩ', 'ﺪ', null, null], 'ذ' => ['ﺫ', 'ﺬ', null, null],
        'ر' => ['ﺭ', 'ﺮ', null, null], 'ز' => ['ﺯ', 'ﺰ', null, null],
        'س' => ['ﺱ', 'ﺲ', 'ﺳ', 'ﺴ'], 'ش' => ['ﺵ', 'ﺶ', 'ﺷ', 'ﺸ'],
        'ص' => ['ﺹ', 'ﺺ', 'ﺻ', 'ﺼ'], 'ض' => ['ﺽ', 'ﺾ', 'ﺿ', 'ﻀ'],
        'ط' => ['ﻁ', 'ﻂ', 'ﻃ', 'ﻄ'], 'ظ' => ['ﻅ', 'ﻆ', 'ﻇ', 'ﻈ'],
        'ع' => ['ﻉ', 'ﻊ', 'ﻋ', 'ﻌ'], 'غ' => ['ﻍ', 'ﻎ', 'ﻏ', 'ﻐ'],
        'ف' => ['ﻑ', 'ﻒ', 'ﻓ', 'ﻔ'], 'ق' => ['ﻕ', 'ﻖ', 'ﻗ', 'ﻘ'],
        'ك' => ['ﻙ', 'ﻚ', 'ﻛ', 'ﻜ'], 'ل' => ['ﻝ', 'ﻞ', 'ﻟ', 'ﻠ'],
        'م' => ['ﻡ', 'ﻢ', 'ﻣ', 'ﻤ'], 'ن' => ['ﻥ', 'ﻦ', 'ﻧ', 'ﻨ'],
        'ه' => ['ﻩ', 'ﻪ', 'ﻫ', 'ﻬ'], 'و' => ['ﻭ', 'ﻮ', null, null],
        'ى' => ['ﻯ', 'ﻰ', null, null], 'ي' => ['ﻱ', 'ﻲ', 'ﻳ', 'ﻴ'],
        'پ' => ['ﭖ', 'ﭗ', 'ﭘ', 'ﭙ'], 'چ' => ['ﭺ', 'ﭻ', 'ﭼ', 'ﭽ'],
        'گ' => ['ﮒ', 'ﮓ', 'ﮔ', 'ﮕ'],
    ];

    public function shapeHtml(string $html): string
    {
        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $nodes = (new DOMXPath($document))->query('//text()[not(ancestor::style) and not(ancestor::script)]');
        if ($nodes !== false) {
            foreach ($nodes as $node) {
                $node->nodeValue = $this->shapeText($node->nodeValue ?? '');
            }
        }

        return (string) preg_replace('/^<\?xml encoding="UTF-8"\?>/', '', (string) $document->saveHTML());
    }

    public function shapeText(string $text): string
    {
        return (string) preg_replace_callback(
            '/[\x{0600}-\x{06FF}\x{0750}-\x{077F}](?:[\x{0600}-\x{06FF}\x{0750}-\x{077F}\s،؛؟:،.-]*[\x{0600}-\x{06FF}\x{0750}-\x{077F}])?/u',
            fn (array $match): string => implode('', array_reverse(mb_str_split($this->presentationForms($match[0])))),
            $text,
        );
    }

    private function presentationForms(string $text): string
    {
        $characters = mb_str_split($text);
        $result = [];

        foreach ($characters as $index => $character) {
            if (! isset(self::FORMS[$character])) {
                $result[] = $character;

                continue;
            }

            $previous = $characters[$index - 1] ?? null;
            $next = $characters[$index + 1] ?? null;
            $joinsPrevious = isset(self::FORMS[$previous])
                && self::FORMS[$previous][2] !== null
                && self::FORMS[$character][1] !== null;
            $joinsNext = isset(self::FORMS[$next])
                && self::FORMS[$character][2] !== null
                && self::FORMS[$next][1] !== null;

            $form = $joinsPrevious && $joinsNext ? 3 : ($joinsPrevious ? 1 : ($joinsNext ? 2 : 0));
            $result[] = self::FORMS[$character][$form] ?? self::FORMS[$character][0];
        }

        return implode('', $result);
    }
}
