<?php
/**
 * @package     kwtSMS for Joomla
 * @copyright   Copyright (C) 2025 kwtSMS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace KwtSMS\Component\Kwtsms\Administrator\Service;

defined('_JEXEC') or die;

use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;

/**
 * Template resolver for kwtSMS. Loads SMS message templates from #__kwtsms_templates,
 * selects by locale with English fallback, and replaces placeholder tokens.
 */
final class TemplateResolver
{
    /** @var array<string, array<string, string>> In-memory cache: template_key => ['ar' => body, 'en' => body] */
    private array $cache = [];

    public function __construct(
        private readonly DatabaseInterface $db
    ) {
    }

    /**
     * Resolve a template by key and locale, replacing placeholder tokens.
     *
     * @param string   $templateKey  Template key (e.g. 'order_new', 'user_registration')
     * @param string   $locale       Joomla locale string (e.g. 'ar-AA', 'en-GB')
     * @param string[] $placeholders Associative array of token => value (e.g. ['customer_name' => 'Ahmed'])
     *
     * @return string The resolved message body, or empty string if template not found
     */
    public function resolve(string $templateKey, string $locale, array $placeholders): string
    {
        // Determine language: ar if locale starts with 'ar', otherwise en
        $lang = str_starts_with(strtolower($locale), 'ar') ? 'ar' : 'en';

        $bodies = $this->fetchBodies($templateKey);
        $body   = $bodies[$lang] ?? $bodies['en'] ?? null;

        if ($body === null) {
            return '';
        }

        // Replace {key} tokens with placeholder values.
        // Strip control characters from values to prevent SMS injection.
        foreach ($placeholders as $token => $value) {
            $safe = preg_replace('/[\x00-\x1F\x7F]/u', ' ', (string) $value) ?? '';
            $body = str_replace('{' . $token . '}', trim($safe), $body);
        }

        return $body;
    }

    /**
     * Get all available templates grouped by template_key.
     *
     * @return array<string, array> Templates keyed by template_key
     */
    public function getAvailableTemplates(): array
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName(['id', 'template_key', 'lang', 'title', 'body', 'placeholders', 'enabled']))
            ->from($this->db->quoteName('#__kwtsms_templates'))
            ->order($this->db->quoteName('template_key') . ' ASC, ' . $this->db->quoteName('lang') . ' ASC');

        $rows = $this->db->setQuery($query)->loadAssocList();

        $grouped = [];

        foreach ($rows as $row) {
            $grouped[$row['template_key']][] = $row;
        }

        return $grouped;
    }

    /**
     * Fetch both AR and EN bodies for a template in one query, with in-memory caching.
     *
     * @return array<string, string> Map of lang => body for available languages
     */
    private function fetchBodies(string $templateKey): array
    {
        if (isset($this->cache[$templateKey])) {
            return $this->cache[$templateKey];
        }

        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName(['lang', 'body']))
            ->from($this->db->quoteName('#__kwtsms_templates'))
            ->where($this->db->quoteName('template_key') . ' = :key')
            ->where($this->db->quoteName('lang') . ' IN (' . $this->db->quote('ar') . ', ' . $this->db->quote('en') . ')')
            ->where($this->db->quoteName('enabled') . ' = 1');

        $query->bind(':key', $templateKey, ParameterType::STRING);

        $rows = $this->db->setQuery($query)->loadAssocList() ?: [];

        $bodies = [];

        foreach ($rows as $row) {
            $bodies[$row['lang']] = (string) $row['body'];
        }

        $this->cache[$templateKey] = $bodies;

        return $bodies;
    }
}
