<?php
/*
 * @package     RadicalMart Messages Email Plugin
 * @subpackage  plg_radicalmart_message_email
 * @version     __DEPLOY_VERSION__
 * @author      RadicalMart Team - radicalmart.ru
 * @copyright   Copyright (c) 2026 RadicalMart. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link        https://radicalmart.ru/
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  object   $user     User data.
 * @var  Registry $customer Customer data.
 * @var  string   $code     Login code.
 *
 */

$site = Uri::getInstance()->getHost();
?>
<div>
	<?php echo Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_USER_LOGIN_CODE_TITLE', $user->name, $site); ?>
</div>
<div>
	<span><?php echo Text::_('PLG_RADICALMART_MESSAGE_EMAIL_USER_LOGIN'); ?> </span>
	<code>
		<?php echo $user->username; ?>
	</code>
</div>
<div>
	<span><?php echo Text::_('PLG_RADICALMART_MESSAGE_EMAIL_USER_CODE'); ?> </span>
	<code>
		<?php echo $code; ?>
	</code>
</div>