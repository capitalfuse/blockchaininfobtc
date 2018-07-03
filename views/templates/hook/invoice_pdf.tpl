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

<table class="product" width="100%" cellpadding="4" cellspacing="0">
<thead>
<tr>
<th class="header small" width="20%">{l s='Bitcoins Payed' pdf='true' mod='blockchaininfobtc'}</th>
<th class="header small" width="60%">{l s='Transaction' pdf='true' mod='blockchaininfobtc'}</th>
</tr>
</thead>
<tbody>
<tr>
<td class="white">
{math equation="x/y" x=$bits_payed y=100000000} BTC
</td>
<td class="center white">
<a href="{$base_url}/api/tx?txid={$txid}&addr={$addr}">{$txid}</a>
</td>
</tr>

</tbody>
</table>
