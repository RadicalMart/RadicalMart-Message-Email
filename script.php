<?php
/*
 * @package     RadicalMart Payment Payselection Plugin
 * @subpackage  PLG_RADICALMART_MESSAGE_EMAIL
 * @version     __DEPLOY_VERSION__
 * @author      Delo Design - delo-design.ru
 * @copyright   Copyright (c) 2023 Delo Design. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link        https://delo-design.ru/
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Version;
use Joomla\Database\DatabaseDriver;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Registry\Registry;

return new class () implements ServiceProviderInterface {
	public function register(Container $container)
	{
		$container->set(InstallerScriptInterface::class, new class ($container->get(AdministratorApplication::class)) implements InstallerScriptInterface {
			/**
			 * The application object
			 *
			 * @var  AdministratorApplication
			 *
			 * @since  __DEPLOY_VERSION__
			 */
			protected AdministratorApplication $app;

			/**
			 * The Database object.
			 *
			 * @var   DatabaseDriver
			 *
			 * @since  __DEPLOY_VERSION__
			 */
			protected DatabaseDriver $db;

			/**
			 * Minimum Joomla version required to install the extension.
			 *
			 * @var  string
			 *
			 * @since  __DEPLOY_VERSION__
			 */
			protected string $minimumJoomla = '4.2';

			/**
			 * Minimum PHP version required to install the extension.
			 *
			 * @var  string
			 *
			 * @since  __DEPLOY_VERSION__
			 */
			protected string $minimumPhp = '7.4';

			/**
			 * Update methods.
			 *
			 * @var  array
			 *
			 * @since  __DEPLOY_VERSION__
			 */
			protected array $updateMethods = [];

			/**
			 * Constructor.
			 *
			 * @param   AdministratorApplication  $app  The application object.
			 *
			 * @since __DEPLOY_VERSION__
			 */
			public function __construct(AdministratorApplication $app)
			{
				$this->app = $app;
				$this->db  = Factory::getContainer()->get('DatabaseDriver');
			}

			/**
			 * Function called after the extension is installed.
			 *
			 * @param   InstallerAdapter  $adapter  The adapter calling this method
			 *
			 * @return  boolean  True on success
			 *
			 * @since   __DEPLOY_VERSION__
			 */
			public function install(InstallerAdapter $adapter): bool
			{
				$this->enablePlugin($adapter);

				return true;
			}

			/**
			 * Function called after the extension is updated.
			 *
			 * @param   InstallerAdapter  $adapter  The adapter calling this method
			 *
			 * @return  boolean  True on success
			 *
			 * @since   __DEPLOY_VERSION__
			 */
			public function update(InstallerAdapter $adapter): bool
			{
				return true;
			}

			/**
			 * Function called after the extension is uninstalled.
			 *
			 * @param   InstallerAdapter  $adapter  The adapter calling this method
			 *
			 * @return  boolean  True on success
			 *
			 * @since   __DEPLOY_VERSION__
			 */
			public function uninstall(InstallerAdapter $adapter): bool
			{
				return true;
			}

			/**
			 * Function called before extension installation/update/removal procedure commences.
			 *
			 * @param   string            $type     The type of change (install or discover_install, update, uninstall)
			 * @param   InstallerAdapter  $adapter  The adapter calling this method
			 *
			 * @return  boolean  True on success
			 *
			 * @since   1.0.1
			 */
			public function preflight(string $type, InstallerAdapter $adapter): bool
			{
				// Check compatible
				if (!$this->checkCompatible())
				{
					return false;
				}

				if ($type === 'update')
				{
					// Check update server
					$this->changeUpdateServer();
				}

				return true;
			}

			/**
			 * Function called after extension installation/update/removal procedure commences.
			 *
			 * @param   string            $type     The type of change (install or discover_install, update, uninstall)
			 * @param   InstallerAdapter  $adapter  The adapter calling this method
			 *
			 * @return  boolean  True on success
			 *
			 * @since   __DEPLOY_VERSION__
			 */
			public function postflight(string $type, InstallerAdapter $adapter): bool
			{
				$installer = $adapter->getParent();
				// Run updates script
				if ($type === 'update')
				{
					foreach ($this->updateMethods as $method)
					{
						if (method_exists($this, $method))
						{
							$this->$method($adapter);
						}
					}
				}

				return true;
			}

			/**
			 * Method to change current update server.
			 *
			 * @throws  \Exception
			 *
			 * @since  1.1.1
			 */
			protected function changeUpdateServer()
			{
				$old = 'https://radicalmart.ru/update?element=plg_radicalmart_message_email';
				$new = 'https://sovmart.ru/update?element=plg_radicalmart_message_email';

				$db    = $this->db;
				$query = $db->getQuery(true)
					->select(['update_site_id', 'location'])
					->from($db->quoteName('#__update_sites'))
					->where($db->quoteName('location') . ' = :location')
					->bind(':location', $old);
				if ($update = $db->setQuery($query)->loadObject())
				{
					$update->location = $new;
					$db->updateObject('#__update_sites', $update, 'update_site_id');
				}
			}

			/**
			 * Method to check compatible.
			 *
			 * @throws  \Exception
			 *
			 * @return  bool True on success, False on failure.
			 *
			 * @since  __DEPLOY_VERSION__
			 */
			protected function checkCompatible(): bool
			{
				$app = Factory::getApplication();

				// Check joomla version
				if (!(new Version())->isCompatible($this->minimumJoomla))
				{
					$app->enqueueMessage(Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_ERROR_COMPATIBLE_JOOMLA', $this->minimumJoomla),
						'error');

					return false;
				}

				// Check PHP
				if (!(version_compare(PHP_VERSION, $this->minimumPhp) >= 0))
				{
					$app->enqueueMessage(Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_ERROR_COMPATIBLE_PHP', $this->minimumPhp),
						'error');

					return false;
				}

				return true;
			}

			/**
			 * Enable plugin after installation.
			 *
			 * @param   InstallerAdapter  $adapter  Parent object calling object.
			 *
			 * @since  1.0.0
			 */
			protected function enablePlugin(InstallerAdapter $adapter)
			{
				// Prepare plugin object
				$plugin          = new \stdClass();
				$plugin->type    = 'plugin';
				$plugin->element = $adapter->getElement();
				$plugin->folder  = (string) $adapter->getParent()->manifest->attributes()['group'];
				$plugin->enabled = 1;

				// Update record
				$this->db->updateObject('#__extensions', $plugin, ['type', 'element', 'folder']);
			}
		});
	}
};