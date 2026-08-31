<?php

namespace App\Services;

/**
 * Parses a contact-export file (CSV or vCard) into a flat list of
 * ['name' => ..., 'phone' => ..., 'email' => ...] rows, ready for the
 * caller to validate and turn into Customer records.
 *
 * Deliberately tolerant of the real-world exports people actually have —
 * Google Contacts CSV, Outlook CSV, and iPhone/Android vCard — rather than
 * requiring one exact column layout.
 */
class ContactImportParser
{
    // Exact (not substring) header matches for name columns — "name" is too
    // generic a substring to fuzzy-match: it also appears inside "given name"
    // and "family name", so a loose match would double up a contact's name
    // when a CSV has both a whole-name column AND separate given/family ones
    // (Google Contacts exports this way).
    private const FULL_NAME_HEADERS  = ['name', 'full name', 'display name'];
    private const GIVEN_NAME_HEADERS = ['given name', 'first name'];
    private const LAST_NAME_HEADERS  = ['family name', 'last name', 'surname'];

    /** @var string[] Header fragments (lowercased, substring match) that identify a phone/email column. */
    private const PHONE_HEADERS = ['mobile', 'cell', 'phone', 'tel'];
    private const EMAIL_HEADERS = ['e-mail', 'email'];

    /**
     * @return array<int, array{name: ?string, phone: ?string, email: ?string}>
     */
    public function parse(string $contents, string $filename): array
    {
        $isVCard = str_contains($contents, 'BEGIN:VCARD') || str_ends_with(strtolower($filename), '.vcf');

        return $isVCard ? $this->parseVCard($contents) : $this->parseCsv($contents);
    }

    // ─────────────────────────────────────────────────────────────────── CSV

    private function parseCsv(string $contents): array
    {
        // Strip a UTF-8 BOM — Excel/Outlook exports commonly include one,
        // which would otherwise corrupt the first header's match.
        $contents = preg_replace('/^\xEF\xBB\xBF/', '', $contents);

        $lines = array_values(array_filter(preg_split('/\r\n|\r|\n/', $contents), fn ($l) => trim($l) !== ''));
        if (empty($lines)) {
            return [];
        }

        $header = str_getcsv(array_shift($lines), ',', '"', '');
        $header = array_map(fn ($h) => strtolower(trim($h)), $header);

        // A whole-name column takes priority and is used as-is; given/family
        // are only combined when there's no whole-name column to prefer.
        $fullNameCol  = $this->findExactColumn($header, self::FULL_NAME_HEADERS);
        $givenNameCol = $fullNameCol === null ? $this->findExactColumn($header, self::GIVEN_NAME_HEADERS) : null;
        $lastNameCol  = $fullNameCol === null ? $this->findExactColumn($header, self::LAST_NAME_HEADERS) : null;
        $phoneCol     = $this->findColumn($header, self::PHONE_HEADERS);
        $emailCol     = $this->findColumn($header, self::EMAIL_HEADERS);

        $rows = [];
        foreach ($lines as $line) {
            $fields = str_getcsv($line, ',', '"', '');
            if (empty(array_filter($fields, fn ($f) => trim((string) $f) !== ''))) {
                continue;
            }

            if ($fullNameCol !== null) {
                $name = trim((string) ($fields[$fullNameCol] ?? ''));
            } else {
                $given = $givenNameCol !== null ? trim((string) ($fields[$givenNameCol] ?? '')) : '';
                $last  = $lastNameCol !== null ? trim((string) ($fields[$lastNameCol] ?? '')) : '';
                $name  = trim($given . ' ' . $last);
            }

            $rows[] = [
                'name'  => $name !== '' ? $name : null,
                'phone' => $phoneCol !== null ? trim((string) ($fields[$phoneCol] ?? '')) ?: null : null,
                'email' => $emailCol !== null ? trim((string) ($fields[$emailCol] ?? '')) ?: null : null,
            ];
        }

        return $rows;
    }

    /** First header index whose text contains any of the given fragments (substring match). */
    private function findColumn(array $header, array $fragments): ?int
    {
        foreach ($fragments as $fragment) {
            foreach ($header as $i => $col) {
                if (str_contains($col, $fragment)) {
                    return $i;
                }
            }
        }
        return null;
    }

    /** First header index that exactly equals one of the given values. */
    private function findExactColumn(array $header, array $exactValues): ?int
    {
        foreach ($exactValues as $value) {
            $i = array_search($value, $header, true);
            if ($i !== false) {
                return $i;
            }
        }
        return null;
    }

    // ────────────────────────────────────────────────────────────────── vCard

    private function parseVCard(string $contents): array
    {
        // Unfold RFC 2426 continuation lines (a line starting with a space
        // or tab is a continuation of the previous line).
        $contents = preg_replace('/\r\n[ \t]/', '', $contents);
        $contents = preg_replace('/\n[ \t]/', '', $contents);

        $lines = preg_split('/\r\n|\r|\n/', $contents);

        $rows    = [];
        $current = null;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (strcasecmp($trimmed, 'BEGIN:VCARD') === 0) {
                $current = ['name' => null, 'phone' => null, 'email' => null];
                continue;
            }
            if (strcasecmp($trimmed, 'END:VCARD') === 0) {
                if ($current && ($current['name'] || $current['phone'] || $current['email'])) {
                    $rows[] = $current;
                }
                $current = null;
                continue;
            }
            if ($current === null || !str_contains($trimmed, ':')) {
                continue;
            }

            [$key, $value] = explode(':', $trimmed, 2);
            $keyUpper = strtoupper(explode(';', $key)[0]);

            if ($keyUpper === 'FN' && !$current['name']) {
                $current['name'] = trim($value);
            } elseif ($keyUpper === 'N' && !$current['name']) {
                // N:Family;Given;Middle;Prefix;Suffix
                $parts = explode(';', $value);
                $built = trim(($parts[1] ?? '') . ' ' . ($parts[0] ?? ''));
                if ($built !== '') {
                    $current['name'] = $built;
                }
            } elseif ($keyUpper === 'TEL' && !$current['phone']) {
                $current['phone'] = trim($value);
            } elseif ($keyUpper === 'EMAIL' && !$current['email']) {
                $current['email'] = trim($value);
            }
        }

        return $rows;
    }
}
