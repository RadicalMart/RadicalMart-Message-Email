<?php
/*
 * @package     RadicalMart Payment Payselection Plugin
 * @subpackage  PLG_RADICALMART_MESSAGE_EMAIL
 * @version     2.2.0
 * @author      RadicalMart Team - radicalmart.ru
 * @copyright   Copyright (c) 2026 RadicalMart. All rights reserved.
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
				// Create plugin class
				$subject = $container->get(DispatcherInterface::class);
				$config  = (array) PluginHelper::getPlugin('radicalmart_message', 'email');
				$plugin  = new Email($subject, $config);

				// Set application
				$app = Factory::getApplication();
				$plugin->setApplication($app);

				return $plugin;
			}
		);
	}
};