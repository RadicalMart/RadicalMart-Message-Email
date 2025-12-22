<?php
/*
 * @package     RadicalMart Payment Payselection Plugin
 * @subpackage  PLG_RADICALMART_MESSAGE_EMAIL
 * @version     2.1.1
 * @author      RadicalMart Team - radicalmart.ru
 * @copyright   Copyright (c) 2024 RadicalMart. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link        https://radicalmart.ru/
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\Plugin\RadicalMartMessage\Email\Extension\Email;

return new class implements ServiceProviderInterface {

	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param   Container  $container  The DI container.
	 *
	 * @since   2.0.0
	 */
	public function register(Container $container): void
	{
		$container->set(PluginInterface::class,
			function (Container $container) {
				$plugin  = PluginHelper::getPlugin('radicalmart_message', 'email');
				$subject = $container->get(DispatcherInterface::class);

				$plugin = new Email($subject, (array) $plugin);
				$plugin->setApplication(Factory::getApplication());

				return $plugin;
			}
		);
	}
};