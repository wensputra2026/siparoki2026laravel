<?php

namespace App\Services;

class ProfanityFilterService
{
    /**
     * Comprehensive list of bad words, curses, vulgarities, hate speech, and toxic words
     * in Indonesian, English, and common slang.
     */
    protected static array $badWords = [
        // Indonesian harsh curses & insults
        'anjing', 'anjing', 'anjir', 'anjrit', 'asw', 'ajg', 'babi', 'babi', 'monyet', 'kunyuk',
        'bajingan', 'bangsat', 'keparat', 'brengsek', 'kontol', 'memek', 'jembut', 'itil',
        'pantek', 'pepek', 'titit', 'tetek', 'toket', 'ngentot', 'ngewe', 'coly', 'coli',
        'bokep', 'porno', 'seks', 'vagina', 'penis', 'lonte', 'perek', 'pelacur', 'sundal',
        'perek', 'bitch', 'fuck', 'shit', 'asshole', 'bastard', 'cunt', 'dick', 'pussy',
        'whore', 'slut', 'idiot', 'tolol', 'goblok', 'bego', 'idiot', 'bebal', 'dungu',
        'bloon', 'kampret', 'sialan', 'setan', 'iblis', 'jancuk', 'jancok', 'dancok',
        'cuk', 'matamu', 'ndasmu', 'asu', 'asu', 'peli', 'tempik', 'peler', 'pler',
        'tae', 'tai', 'taik', 'telek', 'modar', 'mampus', 'bangke', 'bangkai', 'sontoloyo',
        'kentu', 'crot', 'pecun', 'jablay', 'kimak', 'puki', 'pukimak', 'bodat', 'bedebah',
        'kampang', 'pantek', 'palui', 'bencong', 'banci', 'homo', 'lesbi', 'gay', 'lgbtq',
        'kafir', 'sesat', 'teroris', 'biadab', 'laknat', 'haram jadah', 'anak haram',
        // Common obfuscations / leetspeak
        'k0nt0l', 'm3m3k', 'b4ngs4t', '4nj1ng', 'b4b1', 'j4ncuk', 'ng3nt0t', 'p0rn0',
    ];

    /**
     * Check if a string contains vulgarity/profanity or spam patterns.
     *
     * @param string $text
     * @return array{isClean: bool, flaggedWords: array, sanitizedText: string, reason: string|null}
     */
    public static function check(string $text): array
    {
        if (empty(trim($text))) {
            return [
                'isClean' => true,
                'flaggedWords' => [],
                'sanitizedText' => '',
                'reason' => null,
            ];
        }

        $flaggedWords = [];
        $sanitized = $text;

        // 1. Check for spam links (e.g., slot, judi, casino, suspicious URL spam)
        $spamPatterns = [
            '/\b(slot|gacor|judi|casino|togel|poker|sbobet|maxwin|pragmatic|zeus)\b/i',
            '/(https?:\/\/[^\s]+)/i',
        ];

        foreach ($spamPatterns as $pattern) {
            if (preg_match_all($pattern, $text, $matches)) {
                foreach ($matches[0] as $match) {
                    $flaggedWords[] = $match;
                }
            }
        }

        // 2. Normalize text for word detection (strip common repeated chars & symbols)
        $normalized = strtolower($text);
        // Replace leetspeak numbers with letters for checking
        $leetMap = ['0' => 'o', '1' => 'i', '3' => 'e', '4' => 'a', '5' => 's', '8' => 'b', '@' => 'a', '$' => 's'];
        $normalizedLeet = strtr($normalized, $leetMap);

        foreach (self::$badWords as $badWord) {
            $wordLen = strlen($badWord);
            // Check word boundary or contained word if >= 4 chars
            if ($wordLen <= 3) {
                $regex = '/\b' . preg_quote($badWord, '/') . '\b/i';
            } else {
                $regex = '/\b' . preg_quote($badWord, '/') . '\b|' . preg_quote($badWord, '/') . '/i';
            }

            if (preg_match($regex, $normalized) || preg_match($regex, $normalizedLeet)) {
                $flaggedWords[] = $badWord;
                // Mask in sanitized text
                $replacement = str_repeat('*', $wordLen);
                $sanitized = preg_replace('/' . preg_quote($badWord, '/') . '/i', $replacement, $sanitized);
            }
        }

        $flaggedWords = array_values(array_unique($flaggedWords));
        $isClean = count($flaggedWords) === 0;

        return [
            'isClean' => $isClean,
            'flaggedWords' => $flaggedWords,
            'sanitizedText' => $sanitized,
            'reason' => !$isClean ? 'Mengandung kata atau tautan yang memerlukan moderasi (' . implode(', ', array_slice($flaggedWords, 0, 5)) . ')' : null,
        ];
    }
}
