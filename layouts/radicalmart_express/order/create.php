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

defined('_JEXEC') or die;

echo \Joomla\Component\RadicalMartExpress\Administrator\Helper\LayoutsHelper::renderSiteLayout(
	'plugins.radicalmart_message.email.radicalmart_express.order.change_status',
	$displayData
);
