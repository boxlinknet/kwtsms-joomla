<?php

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

        $body = $this->fetchBody($templateKey, $lang);

        // Fallback to English if not found in requested language
        if ($body === null && $lang !== 'en') {
            $body = $this->fetchBody($templateKey, 'en');
        }

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
     * Fetch the body of a template by key and language.
     *
     * @return string|null Body text, or null if not found or disabled
     */
    private function fetchBody(string $templateKey, string $lang): ?string
    {
        $query = $this->db->getQuery(true)
            ->select($this->db->quoteName('body'))
            ->from($this->db->quoteName('#__kwtsms_templates'))
            ->where($this->db->quoteName('template_key') . ' = :key')
            ->where($this->db->quoteName('lang') . ' = :lang')
            ->where($this->db->quoteName('enabled') . ' = 1');

        $query->bind(':key', $templateKey, ParameterType::STRING);
        $query->bind(':lang', $lang, ParameterType::STRING);

        $result = $this->db->setQuery($query)->loadResult();

        return $result === null ? null : (string) $result;
    }
}
