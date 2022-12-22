<?php
/*
 * @package     RadicalMart Package
 * @subpackage  plg_radicalmart_message_email
 * @version     __DEPLOY_VERSION__
 * @author      Delo Design - delo-design.ru
 * @copyright   Copyright (c) 2021 Delo Design. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link        https://delo-design.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
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
	 * Add email config form to RadicalMart.
	 *
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @throws  Exception
	 *
	 * @since  1.1.0
	 */
	public function onRadicalMartPrepareConfigForm($form, $data)
	{
		Form::addFormPath(__DIR__ . '/forms');
		$form->loadFile('radicalmart');
	}

	/**
	 * Add email config form to RadicalMart Express.
	 *
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @throws  Exception
	 *
	 * @since  1.1.0
	 */
	public function onRadicalMartExpressPrepareConfigForm($form, $data)
	{
		Form::addFormPath(__DIR__ . '/forms');
		$form->loadFile('radicalmart_express');
	}

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
		if (!in_array($type, ['radicalmart.user.create', 'radicalmart.order.create', 'radicalmart.order.change_status']))
		{
			return;
		}

		$helper = '\\Joomla\\Component\\RadicalMart\\Administrator\\Helper\\MessageHelper';
		$params = \Joomla\Component\RadicalMart\Administrator\Helper\ParamsHelper::getComponentParams();
		$layout = 'plugins.radicalmart_message.email.' . $type;

		if (in_array($type, ['radicalmart.order.create', 'radicalmart.order.change_status']))
		{
			// Send order message
			$config  = Factory::getConfig();
			$subject = ($type === 'radicalmart.order.create')
				? Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_ORDER_CREATE', $data->number)
				: Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_ORDER_CHANGE_STATUS', $data->number, Text::_($data->status->title));

			// Send client email
			if (!empty($data->contacts['email']))
			{
				$this->sendEmail($subject, $data->contacts['email'],
					$helper::renderLayout($layout, [
						'recipient' => 'client',
						'order'     => $data,
						'params'    => $params,
					]));
			}

			// Send admin email
			$adminEmails = [];
			if (!empty($params->get('messages_email_admin')))
			{
				foreach ((array) $params->get('messages_email_admin') as $param)
				{
					if (!empty($param->email))
					{
						$adminEmails[] = $param->email;
					}
				}
			}

			if (empty($adminEmails))
			{
				$adminEmails[] = $config->get('replyto', $config->get('mailfrom'));
			}

			$this->sendEmail($subject, $adminEmails,
				$helper::renderLayout($layout, [
					'recipient' => 'admin',
					'order'     => $data,
					'params'    => $params,
				]));
		}
		elseif (($type === 'radicalmart.user.create') && !empty($data['result']))
		{
			// Send user email
			$subject = Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_USER_CREATE', $data['user']->name,
				Uri::getInstance()->getHost());

			$recipient = $data['user']->email;
			$body      = $helper::renderLayout($layout, ['user' => $data]);

			// Send email
			$this->sendEmail($subject, $recipient, $body);
		}
	}

	/**
	 * Method to send message.
	 *
	 * @param   string  $type  Message type.
	 * @param   mixed   $data  Message data.
	 *
	 * @throws Exception
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	public function onRadicalMartExpressSendMessage($type = null, $data = null)
	{
		if (!in_array($type, ['radicalmart_express.user.create', 'radicalmart_express.order.create',
			'radicalmart_express.order.change_status']))
		{
			return;
		}

		$helper = 'RadicalMartHelperMessage';
		$params = ComponentHelper::getParams('com_radicalmart_express');
		$layout = 'plugins.radicalmart_message.email.' . $type;

		if (in_array($type, ['radicalmart_express.order.create', 'radicalmart_express.order.change_status']))
		{
			// Send order message
			$config  = Factory::getConfig();
			$subject = ($type === 'radicalmart_express.order.create')
				? Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_ORDER_CREATE', $data->number)
				: Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_ORDER_CHANGE_STATUS', $data->number, Text::_($data->status->title));

			$links = true;
			if ($data->status->id !== 2)
			{
				$links = false;
			}

			// Send client email
			if (!empty($data->contacts['email']))
			{
				$this->sendEmail($subject, $data->contacts['email'],
					$helper::renderLayout($layout, [
						'recipient' => 'client',
						'order'     => $data,
						'params'    => $params,
						'links'     => $links,
					]));
			}

			// Send admin email
			$adminEmails = [];
			if (!empty($params->get('messages_email_admin')))
			{
				foreach ((array) $params->get('messages_email_admin') as $param)
				{
					if (!empty($param->email))
					{
						$adminEmails[] = $param->email;
					}
				}
			}

			if (empty($adminEmails))
			{
				$adminEmails[] = $config->get('replyto', $config->get('mailfrom'));
			}

			$this->sendEmail($subject, $adminEmails,
				$helper::renderLayout($layout, [
					'recipient' => 'admin',
					'order'     => $data,
					'params'    => $params,
					'links'     => true,
				]));
		}
		elseif (($type === 'radicalmart_express.user.create') && !empty($data['result']))
		{
			// Send user email
			$subject = Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_USER_CREATE', $data['user']->name,
				Uri::getInstance()->getHost());

			$recipient = $data['user']->email;
			$body      = $helper::renderLayout($layout, ['user' => $data]);

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
		//$mailer->addReplyTo($config->get('replyto'), $config->get('replytoname'));
		$mailer->setBody($body);

		return $mailer->Send();
	}
}