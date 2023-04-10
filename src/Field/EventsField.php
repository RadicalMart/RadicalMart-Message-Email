<?php
/*
 * @package     RadicalMart Package
 * @subpackage  plg_radicalmart_message_email
 * @version     __DEPLOY_VERSION__
 * @author      Delo Design - delo-design.ru
 * @copyright   Copyright (c) 2023 Delo Design. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link        https://delo-design.ru/
 */

namespace Joomla\Plugin\RadicalMartMessage\Email\Field;

\defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Language\Text;

class EventsField extends ListField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $type = 'events';

	/**
	 * The form field type.
	 *
	 * @var  string|null
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $extension = null;

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value.
	 *
	 * @return  bool  True on success.
	 *
	 * @since  1.0.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null): bool
	{
		if ($return = parent::setup($element, $value, $group))
		{
			$extension = (!empty($this->element['extension'])) ? (string) $this->element['extension']
				: null;
			if (!empty($extension) && in_array($extension, ['com_radicalmart', 'com_radicalmart_express']))
			{
				$this->extension = $extension;
			}


		}

		return $return;
	}

	/**
	 * Method to get the field options.
	 *
	 * @throws  \Exception
	 *
	 * @return  array  The field option objects.
	 *
	 * @since  1.0.0
	 */
	protected function getOptions(): array
	{
		$options = parent::getOptions();

		if ($this->extension === 'com_radicalmart')
		{
			$this->setRadicalMartStatuses($options);
		}

		return $options;
	}

	protected function setRadicalMartStatuses(&$options)
	{
		$db    = $this->getDatabase();
		$query = $db->getQuery(true)
			->select(['s.id', 's.title'])
			->from($db->quoteName('#__radicalmart_statuses', 's'));
		if ($statuses = $db->setQuery($query)->loadObjectList())
		{
			foreach ($statuses as $status)
			{
				$option        = new \stdClass();
				$option->value = 'radicalmart.order.change_' . $status->id;
				$option->text  = Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_PARAMS_EVENTS_ORDER_CHANGE_STATUS',
					Text::_($status->title));

				$options[] = $option;
			}
		}
	}
}