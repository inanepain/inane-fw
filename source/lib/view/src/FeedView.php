<?php

/**
 * FeedView
 *
 * Inane Library
 *
 * $Id$
 * $Date$
 *
 * PHP version 8.5
 *
 * @author   Philip Michael Raab <philip@cathedral.co.za>
 * @package  inanepain\view
 * @category view
 *
 * @license  UNLICENSE
 * @license  https://unlicense.org/UNLICENSE UNLICENSE
 *
 * _version_ $version
 */

declare(strict_types = 1);

namespace Inane\View;

use DateTimeInterface;
use Inane\Stdlib\Exception\JsonException;
use Inane\Stdlib\Json;
use InvalidArgumentException;

use function gmdate;
use function htmlspecialchars;
use function in_array;
use function sprintf;

use const DATE_ATOM;
use const DATE_RSS;
use const ENT_QUOTES;
use const ENT_XML1;

/**
 * Represents a view class for rendering feed data in multiple formats, such as Atom, RSS, and JSON.
 */
class FeedView extends View {
    /**
     * Feed format: atom
     */
    public const string FORMAT_ATOM = 'atom';
    /**
     * Feed format: rss
     */
    public const string FORMAT_RSS  = 'rss';
    /**
     * Feed format: json
     */
    public const string FORMAT_JSON = 'json';
    /**
     * The format of the feed.
     */
    protected string $format;

    /**
     * Constructor for initializing the instance with data and a specific format.
     *
     * @param array  $data   The initial data to set for the instance.
     * @param string $format The format to set. Default is FORMAT_RSS.
     *
     * @return void
     */
    public function __construct(array $data = [], string $format = self::FORMAT_RSS) {
        parent::__construct($data);
        $this->setFormat($format);
    }

    /**
     * Sets the format of the feed and updates the corresponding content type.
     *
     * @param string $format The format to set. Supported formats are: FORMAT_ATOM, FORMAT_RSS, and FORMAT_JSON.
     *
     * @return self Returns the current instance with the updated format.
     * @throws InvalidArgumentException If the provided format is not supported.
     */
    public function setFormat(string $format): self {
        if (!in_array($format, [
            self::FORMAT_ATOM,
            self::FORMAT_RSS,
            self::FORMAT_JSON,
        ], true)) {
            throw new InvalidArgumentException("Unsupported feed format: {$format}");
        }

        $this->format = $format;

        $this->contentType = match ($format) {
            self::FORMAT_ATOM => 'application/atom+xml',
            self::FORMAT_RSS => 'application/rss+xml',
            self::FORMAT_JSON => 'application/feed+json',
        };

        return $this;
    }

    /**
     * Renders content based on the specified format.
     *
     * @return string The rendered content in the format specified by the `$this->format` property.
     *
     * @throws JsonException
     */
    public function render(): string {
        return match ($this->format) {
            self::FORMAT_ATOM => $this->renderAtom(),
            self::FORMAT_RSS => $this->renderRss(),
            self::FORMAT_JSON => $this->renderJson(),
        };
    }

    /**
     * Renders an Atom XML feed based on the provided data structure.
     *
     * Expected $data structure:
     * [
     *   'title' => string,
     *   'link' => string,
     *   'updated' => DateTimeInterface|null,
     *   'items' => [
     *     [
     *       'id' => string,
     *       'title' => string,
     *       'link' => string,
     *       'updated' => DateTimeInterface|null,
     *       'summary' => string|null,
     *       'content' => string,
     *     ],
     *     ...
     *   ]
     * ]
     *
     * @return string The generated Atom XML feed as a string.
     */
    protected function renderAtom(): string {
        $updated = $this->formatDate($this->data['updated'] ?? null);

        $entries = '';
        foreach($this->data['items'] ?? [] as $item) {
            $entries .= sprintf(
                <<<XML
                <entry>
                    <id>%s</id>
                    <title>%s</title>
                    <link href="%s"/>
                    <updated>%s</updated>
                    %s
                    %s
                </entry>

                XML,
                $this->escape($item['id']),
                $this->escape($item['title']),
                $this->escape($item['link']),
                $this->formatDate($item['updated'] ?? null),
                isset($item['summary']) ? '<summary>' . $this->escape($item['summary']) . '</summary>' : '',
                '<content type="html">' . $this->escape($item['content']) . '</content>',
            );
        }

        return <<<XML
<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
    <title>{$this->escape($this->data['title'] ?? '')}</title>
    <link href="{$this->escape($this->data['link'] ?? '')}"/>
    <updated>{$updated}</updated>
    <id>{$this->escape($this->data['link'] ?? '')}</id>
    {$entries}
</feed>
XML;
    }

    /**
     * Renders the RSS feed as a string.
     *
     * The RSS feed is generated based on the structure of the `$data` property.
     * It includes a channel section containing metadata such as title, link, and description,
     * as well as individual item entries with details like ID, title, link,
     * summary/content, and publication date (if provided).
     *
     * @return string The generated RSS feed in XML format.
     */
    protected function renderRss(): string {
        $itemsXml = '';
        foreach($this->data['items'] ?? [] as $item) {
            $itemsXml .= sprintf(
                <<<XML
                <item>
                    <guid>%s</guid>
                    <title>%s</title>
                    <link>%s</link>
                    <description>%s</description>
                    %s
                </item>

                XML,
                $this->escape($item['id']),
                $this->escape($item['title']),
                $this->escape($item['link']),
                $this->escape($item['summary'] ?? $item['content']),
                isset($item['updated'])
                    ? '<pubDate>' . $this->formatRfc2822($item['updated']) . '</pubDate>'
                    : '',
            );
        }

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<rss version="2.0">
    <channel>
        <title>{$this->escape($this->data['title'] ?? '')}</title>
        <link>{$this->escape($this->data['link'] ?? '')}</link>
        <description>{$this->escape($this->data['description'] ?? '')}</description>
        {$itemsXml}
    </channel>
</rss>
XML;
    }

    /**
     * Renders the feed data into a JSON Feed format (version 1.1).
     *
     * Expected $this->data structure:
     * [
     *   'title' => string,
     *   'link' => string,
     *   'description' => string,
     *   'items' => [
     *     [
     *       'id' => string,
     *       'link' => string,
     *       'title' => string,
     *       'content' => string,
     *       'summary' => string|null,
     *       'updated' => DateTimeInterface|null,
     *     ],
     *     ...
     *   ]
     * ]
     *
     * @return string The JSON-encoded feed data in a pretty-printed format.
     *
     * @throws JsonException If encoding the data to JSON fails.
     */
    protected function renderJson(): string {
        $feed = [
            'version'       => 'https://jsonfeed.org/version/1.1',
            'title'         => $this->data['title'] ?? '',
            'home_page_url' => $this->data['link'] ?? '',
            'description'   => $this->data['description'] ?? '',
            'items'         => [],
        ];

        foreach($this->data['items'] ?? [] as $item) {
            $feed['items'][] = [
                'id'            => $item['id'],
                'url'           => $item['link'],
                'title'         => $item['title'],
                'content_html'  => $item['content'],
                'summary'       => $item['summary'] ?? null,
                'date_modified' => isset($item['updated'])
                    ? $this->formatDate($item['updated'])
                    : null,
            ];
        }

        return Json::encode($feed, ['pretty' => true]);
    }

    /**
     * Formats the given date as a string in the DATE_ATOM format.
     * If no date is provided, the current time in UTC is used.
     *
     * @param DateTimeInterface|null $date The date to format, or null to use the current time.
     *
     * @return string The formatted date in DATE_ATOM format.
     */
    protected function formatDate(?DateTimeInterface $date): string {
        return $date?->format(DATE_ATOM) ?? gmdate(DATE_ATOM);
    }

    /**
     * Formats a given DateTimeInterface object into an RFC 2822 compliant date string.
     *
     * @param DateTimeInterface $date The date object to be formatted.
     *
     * @return string The formatted date string in RFC 2822 format.
     */
    protected function formatRfc2822(DateTimeInterface $date): string {
        return $date->format(DATE_RSS);
    }

    /**
     * Escapes a string for safe use in XML by converting special characters to their corresponding entities.
     *
     * @param string $value The input string to be escaped.
     *
     * @return string The escaped string with special characters converted to XML-safe entities.
     */
    protected function escape(string $value): string {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
