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

{extends file="checkout/checkout.tpl"}

{block name="header_nav"}
  <nav class="header-nav">
    <div class="container">
      <div class="row">
        <div class="hidden-sm-down">
          <div class="col-md-4 col-xs-12">
            <!-- {hook h='displayNav1'} -->
          </div>
        </div>
      </div>
    </div>
  </nav>
{/block}

{block name="header_top"}
<div class="header-top">
  <div class="container">
    <div class="row">
      <div class="col-md-12 hidden-sm-down" id="_desktop_logo">
        <!-- <a href="{$urls.base_url}"> -->
        <a href="{$cancel_url}">
          <img class="logo img-responsive" src="{$shop.logo}" alt="{$shop.name}">
        </a>
      </div>
    </div>
  </div>
</div>
{/block}

{block name="content"}
<section id="content">
  <div class="form" ng-app="bciInvoice">
    <div class="col-md-12 col-xs-12 invoice" ng-controller="CheckoutController">
      <!-- heading row -->
      <div class="row">
        <h3>{l s='Order#' mod='blockchaininfobtc'} {$id_order} {l s=' (Passed away the below time, your order will be cancelled automatically)' mod='blockchaininfobtc'}</h3>
        <span ng-show="{$status} == -1" class="invoice-heading-right" >//clock*1000 | date:'mm:ss' : 'UTC'//</span>
        <div class="progress" ng-hide="{$status} != -1">
          <progress class="progress progress-primary" max="100" value="//progress//">
          </progress>
        </div>
      </div>
      <!-- Amount row -->
      <div class="row">
        <div class="col-md-12 col-xs-12">
          <!-- Status -->
          <h4 ng-init="init({$status},'{$addr}',{$timestamp},'{$base_websocket_url}','{$redirect_link}','{$cancel_url}')" ng-show="{$status} >= 0" for="invoice-amount" style="margin-top:15px;" >{l s='Status' mod='blockchaininfobtc'}</h4>
          <div class="value ng-binding" style="margin-bottom:10px;margin-top:10px" >
            <h3 ng-show="{$status} == -1" >{l s='To pay, Please send exact amount of BTC to the given address' mod='blockchaininfobtc'}</h3></br>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-6 col-xs-6 invoice-amount"  style="border-right:#ccc 1px solid;">
          <!-- address-->
          <div class="row">
            <h3 class="col-md-12 col-xs-12" style="margin-bottom:15px;margin-top:15px;"for="btn-address">{l s='QR CODE(Send Address and Amount)' mod='blockchaininfobtc'}</h3>
          </div>

          <!-- QR Code -->
          <div class="row qr-code-box">
            <div class="col-md-12 col-xs-12 qr-code">
              <div class="qr-enclosure">
                <a target="_blank" href="bitcoin:{$addr}?amount={$bits|string_format:"%.8f"}">
                  <qrcode data="bitcoin:{$addr}?amount={$bits|string_format:"%.8f"}" size="250">
                    <canvas class="qrcode"></canvas>
                  </qrcode></a>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6 col-xs-6 invoice-status" style="margin-bottom:15px;">
          <!-- Amount -->
          <h3 for="invoice-amount">{l s='Amount' mod='blockchaininfobtc'}</h3>
          <div class="value ng-binding">
            <label>{$bits|string_format:"%.8f"}
              <small>BTC</small></label> ⇌
              <label>{$value}
              <small>{$currency_iso_code}</small></label>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="input-group">
          <!-- Necessary to apply text transfrom as some styles will capitalize h4 leading to wrong address -->
          <span class="input-group-addon"><h4>{l s='Bitcoin Send Address' mod='blockchaininfobtc'} -> {$addr}</h4></span>
        </div>
      </div>

      <h3><a target="_blank" href="https://blockchain.info/" title="{l s='Blockchain Info' mod='blockchaininfobtc'}">
        <strong>{l s='Latest Bitcoin Market Price by Blockchain.info ' mod='blockchaininfobtc'}</strong>
      </a> 1BTC = {$bitcoin_rate} {$currency_iso_code}</h3>

      <div class="row">
        <div class="col-md-12 col-xs-12 cancel-button" style="padding:0px; margin-top:15px; margin-bottom:30px;">
          <button type="button" class="btn btn-warning btn-lg" style="float:right;" ng-click="postData('{$cancel_url}')">{l s='Cancel' mod='blockchaininfobtc'}</button>
          <!-- a href="{$cancel_url}" class="btn btn-primary btn-lg active" role="button" aria-pressed="true">{l s='cancel this order' mod='blockchaininfobtc'}</a -->
        </div>
      </div>

    </div>
  </div>
</section>
{/block}
