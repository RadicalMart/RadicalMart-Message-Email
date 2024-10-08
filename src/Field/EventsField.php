<?php
/*
 * @package     RadicalMart Messages Email Plugin
 * @subpackage  plg_radicalmart_message_email
 * @version     __DEPLOY_VERSION__
 * @author      RadicalMart Team - radicalmart.ru
 * @copyright   Copyright (c) 2024 RadicalMart. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link        https://radicalmart.ru/
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
	 * @since  2.0.0
	 */
	protected $type = 'events';

	/**
	 * The form field type.
	 *
	 * @var  string|null
	 *
	 * @since  2.0.0
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
		elseif ($this->extension === 'com_radicalmart_express')
		{
			$this->setRadicalMartExpressStatuses($options);
		}

		return $options;
	}

	/**
	 * Method to add RadicalMart order statuses to options.
	 *
	 * @param   array  $options  Current options array.
	 *
	 * @since  2.0.0
	 */
	protected function setRadicalMartStatuses(array &$options)
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
				$option->value = 'radicalmart.order.change_status.' . $status->id;
				$option->text  = Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_PARAMS_EVENTS_ORDER_CHANGE_STATUS',
					Text::_($status->title));

				$options[] = $option;
			}
		}
	}

	/**
	 * Method to add RadicalMart Express order statuses to options.
	 *
	 * @param   array  $options  Current options array.
	 *
	 * @since  2.0.0
	 */
	protected function setRadicalMartExpressStatuses(array &$options)
	{
		$new        = new \stdClass();
		$new->value = 'radicalmart_express.order.change_status.1';
		$new->text  = Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_PARAMS_EVENTS_ORDER_CHANGE_STATUS',
			Text::_('COM_RADICALMART_EXPRESS_STATUS_NEW'));
		$options[]  = $new;

		$paid        = new \stdClass();
		$paid->value = 'radicalmart_express.order.change_status.2';
		$paid->text  = Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_PARAMS_EVENTS_ORDER_CHANGE_STATUS',
			Text::_('COM_RADICALMART_EXPRESS_STATUS_PAID'));
		$options[]   = $paid;

		$cancel        = new \stdClass();
		$cancel->value = 'radicalmart_express.order.change_status.3';
		$cancel->text  = Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_PARAMS_EVENTS_ORDER_CHANGE_STATUS',
			Text::_('COM_RADICALMART_EXPRESS_STATUS_CANCELED'));
		$options[]     = $cancel;
	}
}