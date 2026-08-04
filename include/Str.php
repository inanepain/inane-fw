<?php

/**
 * KYCDD
 * This code is a part of the KYC DD platform, which provides a comprehensive solution for KYC and other client type processes.
 * PHP version 7.4+
 *
 * @link    https://www.kycdd.co.za
 * @author  Sina Bahrami Raab<sina@kycdd.co.za>
 * @author  Dor Golombick Raab<dor@kycdd.co.za>
 * @author  Philip Michael Raab<philip@kycdd.co.za>
 * @package Kycdd\Platform
 */
declare(strict_types = 1);

class Str {
    /**
     * Checks whether a given domain is allowed based on a list of allowed domains.
     * The method compares the provided domain against a comma-separated list of allowed domains.
     * It performs exact matches and subdomain matches.
     *
     * @param string $domain         The domain to check.
     * @param string $allowedDomains A comma-separated list of allowed domains.
     *
     * @return bool Returns true if the domain is allowed; otherwise, false.
     */
    public static function isAllowedDomain(string $domain, string $allowedDomains): bool {
        $domain = strtolower(trim($domain));
        $domain = rtrim($domain, '.');

        $allowedDomains = str_replace([' '], [''], $allowedDomains);

        foreach(explode(',', $allowedDomains) as $allowed) {
            $allowed = strtolower(trim($allowed));
            $allowed = rtrim($allowed, '.');

            if ($allowed === '') {
                continue;
            }

            // Exact match
            if ($domain === $allowed) {
                return true;
            }

            // Subdomain match
            if (str_ends_with($domain, '.' . $allowed)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves the registered domain from the current HTTP origin or referer, matching it against a list of allowed domains.
     * This function determines the domain from the HTTP_ORIGIN or HTTP_REFERER values provided by the server environment
     * and checks if it matches any domains listed in the allowed domains' configuration.
     * The allowed domains are derived from the `csrf_domains` and `domain` environment variables.
     *
     * @return string|false The matched registered domain if found in the allowed domains list, or false otherwise.
     */
    public static function getRegisteredDomain(): false|string {
        static $matched;
        if (!isset($matched)) {
            $domain = '';
            # If we have been given the HTTP origin, grab the domain from there
            if ($_SERVER['HTTP_ORIGIN']) {
                // HTTP_ORIGIN: "https://subdomain.example.com"
                $domain = $_SERVER['HTTP_ORIGIN'];
            } elseif ($_SERVER['HTTP_REFERER']) { # Or if we've been given the HTTP referer
                // HTTP_REFERER: "https://subdomain.example.com/folder"
                $domain = parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST);
            }

            $allowedDomains = ($_ENV['csrf_domains'] ?? '') . ',' . $_ENV['domain'];

            $domain = $domain
                    |> trim(...)
                    |> strtolower(...)
                    |> (fn($x) => rtrim($x, '.'));

            foreach(explode(',', $allowedDomains) as $allowed) {
                $allowed = $allowed
                        |> trim(...)
                        |> strtolower(...)
                        |> (fn($x) => rtrim($x, '.'));

                if ($allowed === '') {
                    continue;
                }

                // Exact match
                if ($domain === $allowed) {
                    $matched = $allowed;
                }

                // Subdomain match
                if (str_ends_with($domain, '.' . $allowed)) {
                    $matched = $allowed;
                }
            }

            $matched = false;
        }

        return $matched;
    }

    /**
     * Matches a given host against a list of domains and returns the most specific matching domain.
     * Examples:
     *    $domains = 'example.com,example.co.za,test.org';
     *    matchDomain('example.com', $domains); // example.com
     *    matchDomain('www.example.com', $domains); // example.com
     *    matchDomain('a.b.example.co.za', $domains); // example.co.za
     *    matchDomain('example.org', $domains); // false
     *
     * @param string $host       The host to match, typically a domain name.
     * @param string $domainList A comma-separated list of domains to match against.
     *
     * @return string|false The matched domain if found, or false if no match is found.
     */
    public static function matchDomain(string $host, string $domainList): string|false {
        $host = strtolower(trim($host));

        $domains = explode(',', $domainList)
                |> (fn($x) => array_map(static fn($d) => strtolower(trim($d)), $x))
                |> array_filter(...);

        // Longest first to avoid partial matches.
        usort($domains, static fn($a, $b) => strlen($b) <=> strlen($a));

        foreach($domains as $domain) {
            if (
                $host === $domain ||
                str_ends_with($host, '.' . $domain)
            ) {
                return $domain;
            }
        }

        return false;
    }
}
