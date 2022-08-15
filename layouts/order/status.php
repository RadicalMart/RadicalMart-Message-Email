<?php
/*
 * @package     RadicalMart Package
 * @subpackage  plg_radicalmart_message_email
 * @version     1.1.2
 * @author      Delo Design - delo-design.ru
 * @copyright   Copyright (c) 2021 Delo Design. All rights reserved.
 * @license     GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link        https://delo-design.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  object   $order     The order id.
 * @var  string   $recipient Mail recipient.
 * @var  string   $constant  Language constant.
 * @var  string   $component Component name constant.
 * @var  boolean  $links     Products links.
 * @var  Registry $params    Component params.
 *
 */

$root = Uri::getInstance()->toString(array('scheme', 'host', 'port'));
$link = $root;
$link .= ($recipient === 'admin') ? '/administrator/index.php?option=' . $component . '&task=order.edit&id='
	. $order->id : $order->link;
?>
	<h1>
		<a href="<?php echo $link; ?>">
			<?php echo Text::sprintf('PLG_RADICALMART_MESSAGE_EMAIL_ORDER_INFORMATION', $order->number); ?>
		</a>
	</h1>
	<div style="margin-bottom: 20px;">
		<div>
			<strong><?php echo Text::_($constant . '_STATUS'); ?>: </strong>
			<span><?php echo Text::_($order->status->title); ?></span>
		</div>
		<?php if (!empty($order->shipping)): ?>
			<div>
				<strong><?php echo Text::_($constant . '_SHIPPING'); ?>: </strong>
				<span>
					<?php echo (!empty($order->shipping->order->title)) ?
						$order->shipping->order->title : $order->shipping->title; ?>
				</span>
			</div>
		<?php endif; ?>
		<?php if (!empty($order->payment)): ?>
			<div>
				<strong><?php echo Text::_($constant . '_PAYMENT'); ?>: </strong>
				<span>
					<?php echo (!empty($order->payment->order->title)) ?
						$order->payment->order->title : $order->payment->title; ?>
				</span>
			</div>
		<?php endif; ?>
		<?php if (!empty($order->contacts)): ?>
			<?php foreach ($order->contacts as $key => $value):
				if (empty(trim($value))) continue;

				if ($label = $params->get('fields_' . $key . '_label')) $label = Text::_($label);
				elseif (Factory::getLanguage()->hasKey($constant . '_' . $key))
				{
					$label = Text::_($constant . '_' . $key);
				}
				else $label = $key;
				?>
				<div>
					<strong><?php echo $label ?>: </strong>
					<span><?php echo nl2br($value); ?></span>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
	<table style="width: 100%; border: 1px solid #ddd; border-collapse: collapse;border-spacing: 0;">
		<thead>
		<tr>
			<th style="text-align: left; vertical-align: bottom; font-weight: bold;padding: 8px;line-height: 18px; border-left:1px solid #ddd; ">
				<?php echo Text::_($constant . '_PRODUCT'); ?>
			</th>
			<th style="vertical-align: bottom; font-weight: bold;padding: 8px;line-height: 18px; border-left:1px solid #ddd; text-align: right;">
				<?php echo Text::_($constant . '_PRICE'); ?>
			</th>
			<th style="vertical-align: bottom; font-weight: bold;padding: 8px;line-height: 18px; border-left:1px solid #ddd; text-align: center;">
				<?php echo Text::_($constant . '_QUANTITY'); ?>
			</th>
			<th style=" vertical-align: bottom; font-weight: bold;padding: 8px;line-height: 18px; border-left:1px solid #ddd; text-align: right;">
				<?php echo Text::_($constant . '_SUM'); ?>
			</th>
		</tr>
		</thead>
		<tbody>
		<?php
		$i = 0;
		foreach ($order->products as $p => $product) :
			$style = 'padding: 8px; line-height: 18px; text-align: left; vertical-align: top;border-top: 1px solid #ddd;';
			if ($i % 2) $style .= 'background-color: #f9f9f9;';
			$i++;
			?>
			<tr>
				<td style="<?php echo $style; ?>">
					<?php if ($product->link && $links) : ?>
						<a href="<?php echo $root . $product->link; ?>" style="word-wrap:break-word;"
						   class="uk-link-reset"><?php echo $product->title; ?></a>
					<?php else: ?>
						<?php echo $product->title; ?>
					<?php endif; ?>
				</td>
				<td style="<?php echo $style; ?> text-align: right;border-left: 1px solid #ddd;">
					<?php if ($product->order['discount_enable']): ?>
						<div style="font-size: 12px; color: #ccc">
							<s><?php echo $product->order['base_seo']; ?></s>
							<?php echo ' ( - ' . $product->order['discount_seo'] . ')'; ?>
						</div>
					<?php endif; ?>
					<div>
						<?php echo str_replace(' ', '&nbsp;', $product->order['final_seo']); ?>
					</div>
				</td>
				<td style="<?php echo $style; ?> text-align: center;border-left: 1px solid #ddd;">
					<?php echo $product->order['quantity']; ?>
				</td>
				<td style="<?php echo $style; ?> text-align: right;border-left: 1px solid #ddd;">
					<?php if ($product->order['discount_enable']): ?>
						<div style="font-size: 12px; color: #ccc">
							<s><?php echo $product->order['sum_base_seo']; ?></s>
							<?php echo ' ( - ' . $product->order['discount_seo'] . ')'; ?>
						</div>
					<?php endif; ?>
					<div>
						<strong>
							<?php echo str_replace(' ', '&nbsp;', $product->order['sum_final_seo']); ?>
						</strong>
					</div>
				</td>
			</tr>
		<?php endforeach; ?>
		<?php if (!empty($order->shipping) && !empty($order->shipping->order) && !empty($order->shipping->order->price)):
			$style = 'padding: 8px; line-height: 18px; text-align: left; vertical-align: top;border-top: 1px solid #ddd;';
			if ($i % 2) $style .= 'background-color: #f9f9f9;';
			$i++;
			?>
			<tr>
				<td style="<?php echo $style; ?>">
					<?php echo (!empty($order->shipping->order->title)) ?
						$order->shipping->order->title : $order->shipping->title; ?>
				</td>
				<td style="<?php echo $style; ?> text-align: right;border-left: 1px solid #ddd;">
					<?php if (!empty($order->shipping->order->price['discount_enable'])): ?>
						<div style="font-size: 12px; color: #ccc">
							<s><?php echo $order->shipping->order->price['base_seo']; ?></s>
							<?php echo ' ( - ' . $order->shipping->order->price['discount_seo'] . ')'; ?>
						</div>
					<?php endif; ?>
					<div>
						<?php echo str_replace(' ', '&nbsp;', $order->shipping->order->price['final_seo']); ?>
					</div>
				</td>
				<td style="<?php echo $style; ?> text-align: center;border-left: 1px solid #ddd;">1</td>
				<td style="<?php echo $style; ?> text-align: right;border-left: 1px solid #ddd;">
					<?php if (!empty($order->shipping->order->price['discount_enable'])): ?>
						<div style="font-size: 12px; color: #ccc">
							<s><?php echo $order->shipping->order->price['base_seo']; ?></s>
							<?php echo ' ( - ' . $order->shipping->order->price['discount_seo'] . ')'; ?>
						</div>
					<?php endif; ?>
					<div>
						<?php echo str_replace(' ', '&nbsp;', $order->shipping->order->price['final_seo']); ?>
					</div>
				</td>
			</tr>
		<?php endif; ?>
		</tbody>
		<tfoot>
		<tr>
			<td colspan="3" style="border-top: 1px solid #ddd;"></td>
			<td style="border-top: 1px solid #ddd; text-align: right;">
				<div style="margin-bottom: 5px;">
					<span><?php echo Text::_($constant . '_SUBTOTAL'); ?>: </span>
					<span>
						<?php echo str_replace(' ', '&nbsp;', $order->total['base_seo']); ?>
					</span>
				</div>
				<?php if (!empty($order->total['discount'])): ?>
					<div style="margin-bottom: 5px;">
						<span><?php echo Text::_($constant . '_PRICE_DISCOUNT'); ?>: </span>
						<span>
							<?php echo str_replace(' ', '&nbsp;', $order->total['discount_seo']); ?>
						</span>
					</div>
				<?php endif; ?>
				<?php if ($order->payment && !empty($order->payment->order->price['fee_string'])): ?>
					<div style="margin-bottom: 5px;">
						<span><?php echo Text::_($constant . '_PRICE_FEE'); ?>: </span>
						<span>
							<?php echo str_replace(' ', '&nbsp;', $order->total['fee_seo']); ?>
						</span>
					</div>
				<?php endif; ?>
				<div style="font-size: 18px; padding: 20px">
					<span><?php echo Text::_($constant . '_TOTAL'); ?>: </span>
					<strong>
						<?php echo str_replace(' ', '&nbsp;', $order->total['final_seo']); ?>
					</strong>
				</div>
			</td>
		</tr>
		</tfoot>
	</table>
<?php if ($order->pay && $recipient == 'client'): ?>
	<div style="text-align: center;margin-top:20px;">
		<a href="<?php echo $order->pay; ?>" style="color: #006838;font-size: 22px;">
			<?php echo Text::_($constant . '_PAY'); ?>
		</a>
	</div>
<?php endif; ?>