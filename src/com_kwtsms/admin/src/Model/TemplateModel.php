<?php
/**
 * @package     kwtSMS for Joomla
 * @copyright   Copyright (C) 2025 kwtSMS. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

namespace KwtSMS\Component\Kwtsms\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Filter\InputFilter;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

/**
 * Single template model for com_kwtsms.
 */
final class TemplateModel extends BaseDatabaseModel
{
    /**
     * Get a single template row by ID.
     */
    public function getTemplate(int $id): ?object
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'template_key', 'lang', 'title', 'body', 'placeholders', 'enabled']))
            ->from($db->quoteName('#__kwtsms_templates'))
            ->where($db->quoteName('id') . ' = :id');

        $query->bind(':id', $id, ParameterType::INTEGER);

        return $db->setQuery($query)->loadObject();
    }

    /**
     * Save a template (update only, seeds are managed via SQL).
     *
     * @param array<string, mixed> $data
     */
    public function saveTemplate(array $data): bool
    {
        $filter = InputFilter::getInstance();

        $id    = (int) ($data['id'] ?? 0);
        $title = $filter->clean($data['title'] ?? '', 'STRING');
        $body  = $filter->clean($data['body'] ?? '', 'RAW');
        $enabled = (int) ($data['enabled'] ?? 1);

        if ($id <= 0 || empty($title) || empty($body)) {
            return false;
        }

        // Reject body exceeding 7 SMS pages (7 * 160 = 1120 chars)
        if (mb_strlen($body) > 1120) {
            return false;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__kwtsms_templates'))
            ->set($db->quoteName('title') . ' = :title')
            ->set($db->quoteName('body') . ' = :body')
            ->set($db->quoteName('enabled') . ' = :enabled')
            ->where($db->quoteName('id') . ' = :id');

        $query->bind(':title', $title, ParameterType::STRING);
        $query->bind(':body', $body, ParameterType::STRING);
        $query->bind(':enabled', $enabled, ParameterType::INTEGER);
        $query->bind(':id', $id, ParameterType::INTEGER);

        try {
            $db->setQuery($query)->execute();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }
}
