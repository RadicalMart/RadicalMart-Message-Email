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

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  array $user New user data.
 *
 */

$site = Uri::getInstance()->getHost();
?>
<div>
	<?php echo Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_USER_CREATE_TITLE', $user['user']->name, $site); ?>
</div>
<div>
	<?php echo Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_USER_CREATE_DESC', $site); ?>
</div>
<div>
	<span><?php echo Text::_('PLG_RADICALMART_MESSAGE_EMAIL_USER_LOGIN'); ?> </span>
	<code>
		<?php echo (!empty($user['data']['phone'])) ? $user['data']['phone']
			: $user['user']->username; ?>
	</code>
</div>
<div>
	<span><?php echo Text::_('PLG_RADICALMART_MESSAGE_EMAIL_USER_PASSWORD'); ?> </span>
	<code>
		<?php echo $user['data']['newPassword']; ?>
	</code>
</div>