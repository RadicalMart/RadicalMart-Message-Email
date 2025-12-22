<?php
/*
 * @package     RadicalMart Messages Email Plugin
 * @subpackage  plg_radicalmart_message_email
 * @version     2.1.1
 * @author      RadicalMart Team - radicalmart.ru
 * @copyright   Copyright (c) 2024 RadicalMart. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link        https://radicalmart.ru/
 */

defined('_JEXEC') or die;

echo \Joomla\Component\RadicalMart\Administrator\Helper\LayoutsHelper::renderSiteLayout(
	'plugins.radicalmart_message.email.radicalmart.order.change_status',
	$displayData
);