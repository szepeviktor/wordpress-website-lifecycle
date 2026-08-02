<?php

declare(strict_types=1);

/*
 * Find Unicode Format characters in the WordPress database.
 *
 * @see https://www.unicode.org/reports/tr44/#GC_Values_Table
 * @see https://unicode.org/charts/PDF/U2000.pdf
 *
 * This file is loadable with WP-CLI's --require flag:
 *
 * wp --require=invalid-format-characters.php db invalid-format-characters
 */

namespace SzepeViktor\WordPress\Cli;

use RuntimeException;
use WP_CLI;

use function WP_CLI\Utils\format_items;

final class InvalidFormatCharacters
{
    private const FORMAT_CHARACTER_PATTERN = '\p{Cf}';

    /**
     * Match legitimate emoji ZWJ sequences before matching other Format characters.
     *
     * The SKIP verb prevents joiners in sequences such as the pirate flag,
     * family emoji, and emoji with skin-tone modifiers from being reported.
     */
    private const EMOJI_AWARE_FORMAT_CHARACTER_PATTERN = '(?:\p{Extended_Pictographic}(?:\p{Grapheme_Extend}|\p{Emoji_Modifier})*(?:\x{200D}\p{Extended_Pictographic}(?:\p{Grapheme_Extend}|\p{Emoji_Modifier})*)+)(*SKIP)(*F)|\p{Cf}';

    /**
     * Finds Unicode Format (Cf) characters in all database tables.
     *
     * Format characters can be legitimate in emoji, right-to-left text, and
     * some writing systems, so findings should be reviewed before any
     * replacement is attempted.
     *
     * ## OPTIONS
     *
     * [--ignore-emoji-joiners]
     * : Do not report U+200D joiners in valid emoji sequences.
     *
     * [--format=<format>]
     * : Render results in a particular format.
     * ---
     * default: table
     * options:
     *   - plain
     *   - table
     *   - csv
     *   - json
     *   - yaml
     *   - count
     * ---
     *
     * ## EXAMPLES
     *
     *     $ wp db invalid-format-characters
     *     +------------+--------------+------------------+-------------------+------------------------+
     *     | table      | column       | primary_key_name | primary_key_value | context                |
     *     +------------+--------------+------------------+-------------------+------------------------+
     *     | wp_posts   | post_content | ID               | 42                | Text[U+200B]with marker |
     *     +------------+--------------+------------------+-------------------+------------------------+
     *
     *     $ wp db invalid-format-characters --format=count
     *     1
     *
     * @when after_wp_load
     *
     * @param array<int, string> $args
     * @param array<string, mixed> $assoc_args
     */
    public function __invoke(array $args, array $assoc_args): void
    {
        $pattern = isset($assoc_args['ignore-emoji-joiners'])
            ? self::EMOJI_AWARE_FORMAT_CHARACTER_PATTERN
            : self::FORMAT_CHARACTER_PATTERN;

        $results = WP_CLI::runcommand(
            sprintf(
                "db search '%s' --regex --regex-flags=u --all-tables --format=json",
                $pattern
            ),
            [
                'launch' => false,
                'exit_error' => true,
                'return' => true,
                'parse' => 'json',
                'command_args' => ['--no-color'],
            ]
        );

        if (!is_array($results)) {
            WP_CLI::error('The database search returned an invalid response.');
        }

        $findings = [];

        foreach ($results as $result) {
            if (
                !is_array($result)
                || !isset(
                    $result['table'],
                    $result['column'],
                    $result['match'],
                    $result['primary_key_name'],
                    $result['primary_key_value']
                )
                || !is_string($result['table'])
                || !is_string($result['column'])
                || !is_string($result['match'])
                || !is_string($result['primary_key_name'])
                || (!is_string($result['primary_key_value']) && !is_int($result['primary_key_value']))
            ) {
                WP_CLI::error('The database search returned a malformed finding.');
            }

            $context = preg_replace_callback(
                '/' . $pattern . '/u',
                static fn (array $matches): string => sprintf(
                    '[U+%04X]',
                    self::getCodePoint($matches[0])
                ),
                $result['match']
            );

            if (null === $context) {
                throw new RuntimeException('Unable to render a Unicode Format character.');
            }

            $findings[] = [
                'table' => $result['table'],
                'column' => $result['column'],
                'primary_key_name' => $result['primary_key_name'],
                'primary_key_value' => $result['primary_key_value'],
                'context' => $context,
            ];
        }

        $fields = ['table', 'column', 'primary_key_name', 'primary_key_value', 'context'];
        $format = $assoc_args['format'] ?? 'table';

        if ('plain' === $format) {
            foreach ($findings as $finding) {
                WP_CLI::line(sprintf(
                    '%s:%s:%s:%s:%s',
                    $finding['table'],
                    $finding['column'],
                    $finding['primary_key_name'],
                    $finding['primary_key_value'],
                    $finding['context']
                ));
            }
        } else {
            format_items($format, $findings, $fields);
        }

        if ([] !== $findings) {
            WP_CLI::halt(1);
        }
    }

    private static function getCodePoint(string $character): int
    {
        $bytes = array_values(unpack('C*', $character));
        $first_byte = $bytes[0];

        if ($first_byte < 0x80) {
            return $first_byte;
        }

        if ($first_byte < 0xE0 && isset($bytes[1])) {
            return (($first_byte & 0x1F) << 6)
                | ($bytes[1] & 0x3F);
        }

        if ($first_byte < 0xF0 && isset($bytes[1], $bytes[2])) {
            return (($first_byte & 0x0F) << 12)
                | (($bytes[1] & 0x3F) << 6)
                | ($bytes[2] & 0x3F);
        }

        if ($first_byte < 0xF8 && isset($bytes[1], $bytes[2], $bytes[3])) {
            return (($first_byte & 0x07) << 18)
                | (($bytes[1] & 0x3F) << 12)
                | (($bytes[2] & 0x3F) << 6)
                | ($bytes[3] & 0x3F);
        }

        throw new RuntimeException('Unable to decode a Unicode Format character.');
    }
}

WP_CLI::add_command('db invalid-format-characters', InvalidFormatCharacters::class);
