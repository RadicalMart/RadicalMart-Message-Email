<?php
/*
 * @package     RadicalMart Payment Payselection Plugin
 * @subpackage  PLG_RADICALMART_MESSAGE_EMAIL
 * @version     __DEPLOY_VERSION__
 * @author      RadicalMart Team - radicalmart.ru
 * @copyright   Copyright (c) 2024 RadicalMart. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link        https://radicalmart.ru/
 */

namespace Joomla\Plugin\RadicalMartMessage\Email\Extension;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailerFactoryInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\RadicalMart\Administrator\Helper\LayoutsHelper as RadicalMartLayoutsHelper;
use Joomla\Component\RadicalMart\Administrator\Helper\ParamsHelper as RadicalMartParamsHelper;
use Joomla\Component\RadicalMartExpress\Administrator\Helper\LayoutsHelper as RadicalMartExpressLayoutsHelper;
use Joomla\Component\RadicalMartExpress\Administrator\Helper\ParamsHelper as RadicalMartExpressParamsHelper;
use Joomla\Event\SubscriberInterface;
use Joomla\Utilities\ArrayHelper;

class Email extends CMSPlugin implements SubscriberInterface
{
	/**
	 * Load the language file on instantiation.
	 *
	 * @var    bool
	 *
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Enable on RadicalMart
	 *
	 * @var  bool
	 *
	 * @since  2.0.0
	 */
	public bool $radicalmart = true;

	/**
	 * Enable on RadicalMartExpress
	 *
	 * @var  bool
	 *
	 * @since  2.0.0
	 */
	public bool $radicalmart_express = true;

	/**
	 * Returns an array of events this subscriber will listen to.
	 *
	 * @return  array
	 *
	 * @since   2.0.0
	 */
	public static function getSubscribedEvents(): array
	{
		return [
			'onRadicalMartPrepareConfigForm'        => 'onRadicalMartPrepareConfigForm',
			'onRadicalMartPrepareConfigGroups'      => 'onRadicalMartPrepareConfigGroups',
			'onRadicalMartSendMessage'              => 'onSendMessage',
			'onRadicalMartExpressPrepareConfigForm' => 'onRadicalMartExpressPrepareConfigForm',
			'onRadicalMartExpressSendMessage'       => 'onSendMessage',
		];
	}

	/**
	 * Add email config form to RadicalMart.
	 *
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @throws  \Exception
	 *
	 * @since  1.1.0
	 */
	public function onRadicalMartPrepareConfigForm(Form $form, mixed $data = []): void
	{
		$form->loadFile(JPATH_PLUGINS . '/radicalmart_message/email/forms/radicalmart.xml');
	}

	/**
	 * Metho to add config sections to RadicalMart 3 config.
	 *
	 * @param   array  $groups  Current groups array
	 *
	 * @return void
	 *
	 * @since 2.1.1
	 */
	public function onRadicalMartPrepareConfigGroups(array &$groups): void
	{
		$groups['messages']['sections']['email'] = [
			'title'     => 'PLG_RADICALMART_MESSAGE_EMAIL_PARAMS',
			'key'       => 'messages-email',
			'type'      => 'fieldsets',
			'fieldsets' => [
				'messages_email_global',
				'messages_email_customer',
				'messages_email_admin'
			]
		];
	}

	/**
	 * Add email config form to RadicalMart Express.
	 *
	 * @param   Form   $form  The form to be altered.
	 * @param   mixed  $data  The associated data for the form.
	 *
	 * @throws  \Exception
	 *
	 * @since  1.1.0
	 */
	public function onRadicalMartExpressPrepareConfigForm(Form $form, mixed $data = []): void
	{
		$form->loadFile(JPATH_PLUGINS . '/radicalmart_message/email/forms/radicalmart_express.xml');
	}

	/**
	 * Method to send RadicalMart & RadicalMart Express messages.
	 *
	 * @param   string  $type  Message type.
	 * @param   mixed   $data  Message data.
	 *
	 * @throws /Exception
	 *
	 * @since  2.0.0
	 */
	public function onSendMessage(string $type, mixed $data = null): void
	{
		// Check types
		if (!in_array($type, [
			'radicalmart.user.create',
			'radicalmart.user.login.code',
			'radicalmart.order.create',
			'radicalmart.order.change_status',
			'radicalmart_express.user.create',
			'radicalmart_express.order.create',
			'radicalmart_express.order.change_status'
		]))
		{
			return;
		}

		// Get layouts helper
		$layoutsHelper = false;
		$params        = false;
		if (str_contains($type, 'radicalmart.'))
		{
			$layoutsHelper = RadicalMartLayoutsHelper::class;
			$params        = RadicalMartParamsHelper::getComponentParams();
		}
		elseif (str_contains($type, 'radicalmart_express.'))
		{
			$layoutsHelper = RadicalMartExpressLayoutsHelper::class;
			$params        = RadicalMartExpressParamsHelper::getComponentParams();
		}

		if (!$layoutsHelper || (int) $params->get('messages_email_enabled', 1) === 0)
		{
			return;
		}

		$timeout = (int) $params->get('messages_email_timeout', 15);
		$errors  = [];
		$layout  = 'plugins.radicalmart_message.email.' . $type;

		if (str_contains($type, '.order.'))
		{
			// Orders messages
			$subject = (str_contains($type, '.create'))
				? Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_ORDER_CREATE', $data->number)
				: Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_ORDER_CHANGE_STATUS', Text::_($data->status->title), $data->number);
			$event   = (str_contains($type, '.create')) ? $type : $type . '.' . $data->status->id;

			// Send customer email
			if (!empty($data->contacts['email']))
			{
				$availableEvents = $params->get('messages_email_customer', []);
				$send            = false;
				if (empty($availableEvents))
				{
					$send = true;
				}
				elseif (!in_array(-1, $availableEvents)
					&& (in_array(0, $availableEvents) || in_array($event, $availableEvents)))
				{
					$send = true;
				}
				if ($send)
				{
					try
					{
						$this->sendEmail($subject, $data->contacts['email'],
							$layoutsHelper::renderSiteLayout($layout, [
								'recipient' => 'client',
								'order'     => $data,
								'params'    => $params,
							]),
							$timeout
						);
					}
					catch (\Exception $e)
					{
						$errors[] = Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_ERROR_CUSTOMER_EMAIL', $event,
							$e->getMessage());
					}
				}
			}

			// Send administrator emails
			$recipient      = [];
			$administrators = ArrayHelper::fromObject($params->get('messages_email_admin', new \stdClass()));
			if (empty($administrators))
			{
				$config = $this->getApplication()->getConfig();
				if (!empty($config->get('replyto')))
				{
					$recipient[] = $config->get('replyto');
				}
				else
				{
					$recipient[] = $config->get('mailfrom');
				}
			}
			else
			{
				foreach ($administrators as $administrator)
				{
					if (empty($administrator['email']))
					{
						continue;
					}

					$availableEvents = (!empty($administrator['events'])) ? $administrator['events'] : [];
					if (empty($availableEvents) || in_array(0, $availableEvents) || in_array($event, $availableEvents))
					{
						$recipient[] = $administrator['email'];
					}
				}
			}

			if (!empty($recipient))
			{
				try
				{
					$this->sendEmail($subject, $recipient,
						$layoutsHelper::renderSiteLayout($layout, [
							'recipient' => 'admin',
							'order'     => $data,
							'params'    => $params,
						]),
						$timeout
					);
				}
				catch (\Exception $e)
				{
					$errors[] = Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_ERROR_ADMIN_EMAIL', $event, $e->getMessage());
				}
			}
		}
		elseif (str_contains($type, '.user.create') && !empty($data['result']))
		{
			// Send new customer data email
			$availableEvents = $params->get('messages_email_customer', []);
			$send            = false;
			if (empty($availableEvents))
			{
				$send = true;
			}
			elseif (!in_array(-1, $availableEvents)
				&& (in_array(0, $availableEvents) || in_array($type, $availableEvents)))
			{
				$send = true;
			}

			if (!$send)
			{
				return;
			}

			$subject   = Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_USER_CREATE', $data['user']->name,
				Uri::getInstance()->getHost());
			$recipient = $data['user']->email;

			try
			{
				$this->sendEmail($subject, $recipient,
					$layoutsHelper::renderSiteLayout($layout, ['user' => $data]));
			}
			catch (\Exception $e)
			{
				$errors[] = Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_ERROR_CUSTOMER_EMAIL', $type, $e->getMessage());
			}
		}
		elseif ($type === 'radicalmart.user.login.code' && !empty($data['code']))
		{
			try
			{
				if (empty($data['user']) || empty($data['user']->email))
				{
					throw new \Exception(Text::_('PLG_RADICALMART_MESSAGE_EMAIL_ERROR_EMPTY_RECIPIENT'));
				}

				$subject = Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_USER_LOGIN_CODE', $data['user']->name,
					Uri::getInstance()->getHost());

				$this->sendEmail($subject, $data['user']->email,
					$layoutsHelper::renderSiteLayout($layout, $data));
			}
			catch (\Exception $e)
			{
				$errors[] = Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_ERROR_CUSTOMER_EMAIL', $type, $e->getMessage());
			}
		}

		// Log errors
		if (!empty($errors))
		{
			Log::addLogger([
				'text_file'         => 'plg_radicalmart_message_email.php',
				'text_entry_format' => "{DATETIME}\t{CLIENTIP}\t{MESSAGE}\t{PRIORITY}"],
				Log::ALL, ['plg_radicalmart_message_email']);
			foreach ($errors as $message)
			{
				Log::add($message, Log::ERROR, 'plg_radicalmart_message_email');
			}
		}
	}

	/**
	 * Method to send email.
	 *
	 * @param   string        $subject    The email subject.
	 * @param   array|string  $recipient  The email recipient.
	 * @param   string        $body       The email message body.
	 * @param   int           $timeout    Send mail timeout.
	 *
	 *
	 * @throws /Exception
	 *
	 * @return bool True on success, False on failure.
	 *
	 * @since  1.0.0
	 */
	protected function sendEmail(string $subject, array|string $recipient, string $body, int $timeout = 15): bool
	{
		if (empty($body))
		{
			throw new \Exception(Text::_('PLG_RADICALMART_MESSAGE_EMAIL_ERROR_EMPTY_BODY'));
		}

		// Check recipients
		if (is_array($recipient))
		{
			foreach ($recipient as $r => $value)
			{
				if (str_contains($value, '_rm_ace@'))
				{
					unset($recipient[$r]);
				}
			}
		}
		elseif (str_contains($recipient, '_rm_ace@'))
		{
			$recipient = null;
		}

		if (empty($recipient))
		{
			throw new \Exception(Text::_('PLG_RADICALMART_MESSAGE_EMAIL_ERROR_EMPTY_RECIPIENT'));
		}

		$config = $this->getApplication()->getConfig();
		$mailer = Factory::getContainer()->get(MailerFactoryInterface::class)->createMailer($config);
		$mailer->setSender([$config->get('mailfrom'), $config->get('fromname')]);
		$mailer->setSubject($subject);
		$mailer->isHtml();
		$mailer->Encoding = 'base64';
		$mailer->Timeout  = $timeout;
		$mailer->addRecipient($recipient);
		$mailer->setBody($body);

		return $mailer->Send();
	}
}