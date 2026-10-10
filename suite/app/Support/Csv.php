<?php

namespace App\Support;

/**
 * Formula-injection guard for CSV exports.
 *
 * A cell that starts with = + - @ (or a tab / carriage return) is run as a
 * formula when the file is opened in Excel or Google Sheets. An exported
 * business name like `=HYPERLINK("https://evil.example","Click")` then becomes
 * a live link on the admin's machine. Prefixing an apostrophe makes the
 * spreadsheet treat the cell as text.
 *
 * Every export must pass its rows through Csv::row() before fputcsv().
 */
class Csv
{
    private const FORMULA_TRIGGERS = ['=', '+', '-', '@', "\t", "\r"];

    public static function cell(mixed $value): mixed
    {
        // Numbers the app produced itself (ids, amounts) are left alone, so a
        // negative amount stays a number.
        if (! is_string($value) || $value === '' || is_numeric($value)) {
            return $value;
        }

        return in_array($value[0], self::FORMULA_TRIGGERS, true) ? "'" . $value : $value;
    }

    public static function row(array $cells): array
    {
        return array_map([self::class, 'cell'], $cells);
    }
}
