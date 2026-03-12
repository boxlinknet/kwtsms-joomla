<?php

namespace KwtSMS\Component\Kwtsms\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Database\ParameterType;

/**
 * Templates list model for com_kwtsms.
 */
final class TemplatesModel extends BaseDatabaseModel
{
    /**
     * Get all templates ordered by template_key and lang.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTemplates(): array
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select($db->quoteName(['id', 'template_key', 'lang', 'title', 'enabled']))
            ->from($db->quoteName('#__kwtsms_templates'))
            ->order($db->quoteName('template_key') . ' ASC, ' . $db->quoteName('lang') . ' ASC');

        return $db->setQuery($query)->loadAssocList() ?: [];
    }

    /**
     * Toggle the enabled state of a template.
     *
     * @param int $id      Template ID
     * @param int $enabled 1 to enable, 0 to disable
     */
    public function toggleEnabled(int $id, int $enabled): bool
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__kwtsms_templates'))
            ->set($db->quoteName('enabled') . ' = :enabled')
            ->where($db->quoteName('id') . ' = :id');

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
