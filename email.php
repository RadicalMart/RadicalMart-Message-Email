<?php
/*
 * @package     RadicalMart Package
 * @subpackage  plg_radicalmart_message_email
 * @version     1.0.0
 * @author      Delo Design - delo-design.ru
 * @copyright   Copyright (c) 2021 Delo Design. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link        https://delo-design.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

class plgRadicalMart_MessageEmail extends CMSPlugin
{
	/**
	 * Loads the application object.
	 *
	 * @var  CMSApplication
	 *
	 * @since  1.0.0
	 */
	protected $app = null;

	/**
	 * Affects constructor behavior.
	 *
	 * @var  boolean
	 *
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Method to send message.
	 *
	 * @param   string  $type  Message type.
	 * @param   mixed   $data  Message data.
	 *
	 * @throws Exception
	 *
	 * @since  1.0.0
	 */
	public function onRadicalMartSendMessage($type = null, $data = null)
	{
		if (!in_array($type, array('user.create', 'order.create', 'order.change_status',
			'express.user.create', 'express.order.create', 'express.order.change_status'))) return;

		$constant = 'COM_RADICALMART';
		if (in_array($type, array('express.user.create', 'express.order.create', 'express.order.change_status')))
		{
			$constant .= '_EXPRESS';
		}

		if ($type === 'order.create' || $type === 'order.change_status'
			|| $type === 'express.order.create' || $type === 'express.order.change_status')
		{
			$config  = Factory::getConfig();
			$layout  = ($type === 'order.create' || $type === 'express.order.create')
				? 'create' : 'status';
			$subject = ($type === 'order.create' || $type === 'express.order.create')
				? Text::sprintf('PLG_RADICALMART_MESSAGE_ORDER_CREATE', $data->number)
				: Text::sprintf('PLG_RADICALMART_MESSAGE_ORDER_CHANGE_STATUS', $data->number, Text::_($data->status->title));

			$links = true;
			if (($type === 'express.order.create' || $type === 'express.order.change_status') && $data->status->id !== 2)
			{
				$links = false;
			}

			// Send client email
			if (!empty($data->contacts['email']))
			{
				$this->sendEmail($subject, $data->contacts['email'],
					RadicalMartHelperMessage::renderLayout('email.order.' . $layout,
						array('recipient' => 'client', 'order' => $data, 'constant' => $constant, 'links' => $links)));
			}

			// Send admin email
			$this->sendEmail($subject, $config->get('replyto', $config->get('mailfrom')),
				RadicalMartHelperMessage::renderLayout('email.order.' . $layout,
					array('recipient' => 'admin', 'order' => $data, 'constant' => $constant, 'links' => $links)));
		}
		elseif (($type === 'user.create' || $type === 'express.user.create') && !empty($data['result']))
		{
			// Prepare data
			$subject   = Text::sprintf('PLG_RADICALMART_MESSAGE_USER_CREATE', $data['user']->name,
				Uri::getInstance()->getHost());
			$recipient = $data['user']->email;
			$body      = RadicalMartHelperMessage::renderLayout('email.user.create',
				array('user' => $data, 'constant' => $constant));

			// Send email
			$this->sendEmail($subject, $recipient, $body);
		}
	}

	/**
	 * Method to send email.
	 *
	 * @param   string        $subject    The email subject.
	 * @param   array|string  $recipient  The email recipient.
	 * @param   string        $body       The email message body.
	 *
	 *
	 * @return bool True on success, False on failure.
	 *
	 * @since  1.0.0
	 */
	protected function sendEmail($subject, $recipient, $body)
	{
		$config = Factory::getConfig();

		$mailer = Factory::getMailer();
		$mailer->setSender(array(
			$config->get('mailfrom'),
			$config->get('fromname')
		));
		$mailer->setSubject($subject);
		$mailer->isHtml(true);
		$mailer->Encoding = 'base64';
		$mailer->addRecipient($recipient);
		$mailer->addReplyTo(array(
			$config->get('replyto'),
			$config->get('replytoname')
		));
		$mailer->setBody($body);

		return $mailer->Send();
	}
}