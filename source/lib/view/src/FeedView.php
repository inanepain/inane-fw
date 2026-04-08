<?php

declare(strict_types=1);

namespace Inane\View;

use DateTimeInterface;
use InvalidArgumentException;

class FeedView extends View
{
    public const FORMAT_ATOM = 'atom';
    public const FORMAT_RSS  = 'rss';
    public const FORMAT_JSON = 'json';

    protected string $format;

    /**
     * Expected $data structure:
     * [
     *   'title' => string,
     *   'link' => string,
     *   'description' => string,
     *   'updated' => DateTimeInterface|null,
     *   'items' => [
     *     [
     *       'id' => string,
     *       'title' => string,
     *       'link' => string,
     *       'content' => string,
     *       'summary' => string|null,
     *       'author' => string|null,
     *       'updated' => DateTimeInterface|null,
     *     ],
     *     ...
     *   ]
     * ]
     */
    public function __construct(array $data = [], string $format = self::FORMAT_RSS)
    {
        parent::__construct($data);
        $this->setFormat($format);
    }

    public function setFormat(string $format): self
    {
        if (!in_array($format, [self::FORMAT_ATOM, self::FORMAT_RSS, self::FORMAT_JSON], true)) {
            throw new InvalidArgumentException("Unsupported feed format: {$format}");
        }

        $this->format = $format;

        $this->contentType = match ($format) {
            self::FORMAT_ATOM => 'application/atom+xml',
            self::FORMAT_RSS  => 'application/rss+xml',
            self::FORMAT_JSON => 'application/feed+json',
        };

        return $this;
    }

    public function render(): string
    {
        return match ($this->format) {
            self::FORMAT_ATOM => $this->renderAtom(),
            self::FORMAT_RSS  => $this->renderRss(),
            self::FORMAT_JSON => $this->renderJson(),
        };
    }

    protected function renderAtom(): string
    {
        $updated = $this->formatDate($this->data['updated'] ?? null);

        $entries = '';
        foreach ($this->data['items'] ?? [] as $item) {
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
                '<content type="html">' . $this->escape($item['content']) . '</content>'
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

    protected function renderRss(): string
    {
        $itemsXml = '';
        foreach ($this->data['items'] ?? [] as $item) {
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
                    : ''
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

    protected function renderJson(): string
    {
        $feed = [
            'version' => 'https://jsonfeed.org/version/1.1',
            'title' => $this->data['title'] ?? '',
            'home_page_url' => $this->data['link'] ?? '',
            'description' => $this->data['description'] ?? '',
            'items' => [],
        ];

        foreach ($this->data['items'] ?? [] as $item) {
            $feed['items'][] = [
                'id' => $item['id'],
                'url' => $item['link'],
                'title' => $item['title'],
                'content_html' => $item['content'],
                'summary' => $item['summary'] ?? null,
                'date_modified' => isset($item['updated'])
                    ? $this->formatDate($item['updated'])
                    : null,
            ];
        }

        return json_encode($feed, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

    protected function formatDate(?DateTimeInterface $date): string
    {
        return $date?->format(DATE_ATOM) ?? gmdate(DATE_ATOM);
    }

    protected function formatRfc2822(DateTimeInterface $date): string
    {
        return $date->format(DATE_RSS);
    }

    protected function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}