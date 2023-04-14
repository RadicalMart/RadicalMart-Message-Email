<?php
/*
 * @package     RadicalMart Package
 * @subpackage  plg_radicalmart_message_email
 * @version     2.0.0
 * @author      Delo Design - delo-design.ru
 * @copyright   Copyright (c) 2023 Delo Design. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link        https://delo-design.ru/
 */

defined('_JEXEC') or die;

echo \Joomla\Component\RadicalMart\Administrator\Helper\LayoutsHelper::renderSiteLayout(
	'plugins.radicalmart_message.email.radicalmart.order.change_status',
	$displayData
);