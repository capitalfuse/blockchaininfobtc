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

<div class="panel kpi-container">
<fieldset>

<legend>{l s='Bitcoin Transaction Details' mod='blockchaininfobtc'}</legend>

<div id="info">
<table>
<tr><td>{l s='Bitcoin Address' mod='blockchaininfobtc'}</td> <td> : {$addr}</td></tr>
<tr><td>{l s='Status' mod='blockchaininfobtc'}</td> <td> : {$status}</td></tr>
<tr><td>{l s='Cart Value' mod='blockchaininfobtc'}</td> <td> : {math equation='x/y' x=$bits y=100000000} BTC</td></tr>
{if $txid != ''}
<tr><td>{l s='Amount Paid' mod='blockchaininfobtc'}</td> <td> : {math equation='x/y' x=$bits_payed y=100000000} BTC</td></tr>
<<<<<<< HEAD
<tr><td>{l s='Transaction Link' mod='blockchaininfobtc'}</td> <td> : <a href="{$base_url}/btc/tx/{$txid}"> {$txid} <a></td></tr>
=======
<tr><td>{l s='Transaction Link' mod='blockchaininfobtc'}</td> <td> : <a href="{$base_url}/api/tx?txid={$txid}&addr={$addr}"> {$txid} <a></td></tr>
>>>>>>> 1609f11b694dcacb8508a9d3843b19cd9f657b95
{if $bits != $bits_payed}
<tr><td>{l s='Payment Error' mod='blockchaininfobtc'}</td><td style='color:red'> : {l s='Amount paid not matching cart value' mod='blockchaininfobtc'}</td></tr>
{/if}
{/if}
</table>
</div>

</fieldset>
</div>
