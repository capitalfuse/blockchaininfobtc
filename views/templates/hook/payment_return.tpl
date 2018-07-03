{*
 * 2009-2018 BlockchaininfoBTC
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to ficus.online@gmail.com so we can send you a copy immediately.
 *
 * @author    BlockchaininfoBTC Admin <ficus.online@gmail.com>
 * @copyright 2011-2016 BlockchaininfoBTC
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of BlockchaininfoBTC
 *}

{if $status == 'ok'}
<h3>{l s='Your order on %s is complete.' sprintf=[$shop_name] mod='blockchaininfobtc'}</h3>
<p>
	<br />- {l s='Amount' mod='blockchaininfobtc'} : <span class="price"><strong>{$total}</strong></span>
	<br />- {l s='Reference' mod='blockchaininfobtc'} : <span class="reference"><strong>{$reference}</strong></span>
	<br /><br />{l s='An email has been sent with this information.' mod='blockchaininfobtc'}
</p>
{include file='module:blockchaininfobtc/views/templates/hook/_partials/payment_return_infos.tpl'}

<strong>{l s='We will proceed to send your order after being confirmed your payment.' mod='blockchaininfobtc'}</strong>
<p>
  {l s='If you have questions, comments or concerns, please contact our [1]expert customer support team[/1].' mod='blockchaininfobtc' sprintf=['[1]' => "<a href='{$contact_url}'>", '[/1]' => '</a>']}
</p>
{else}
<h3>{l s='Your order on %s has not been accepted.' sprintf=[$shop_name] mod='blockchaininfobtc'}</h3>
<p>
	<br />- {l s='Reference' mod='blockchaininfobtc'} <span class="reference"> <strong>{$reference}</strong></span>
	<br /><br />{l s='Please, try to order again.' mod='blockchaininfobtc'}
  {l s='If you think this is an error, feel free to contact our [1]expert customer support team[/1].' mod='blockchaininfobtc' sprintf=['[1]' => "<a href='{$contact_url}'>", '[/1]' => '</a>']}
</p>
{/if}
<h3><a target="_blank" href='{$base_url}' title="{l s='Blockchain Info' mod='blockchaininfobtc'}"></a></h3>
<hr />
