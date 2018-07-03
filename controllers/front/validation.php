<?php
/**
* 2007-2018 FicusOnline
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
*  @author    Takanobu Fuse <ficus.online.store@gmail.com>
*  @copyright 2007-2018 FicusOnline
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of FicusOnline
*/

class BlockchaininfobtcValidationModuleFrontController extends ModuleFrontController
{
    public function setMedia()
    {
        parent::setMedia();
        $this->registerStylesheet(
            'mystyle',
            'modules/'.$this->module->name.'/views/css/bootstrap-prestashop-ui-kit.css',
            array('postion' => 'head')
        );
        $this->registerStylesheet(
            'mystyle1',
            'modules/'.$this->module->name.'/views/css/style.css',
            array('postion' => 'head')
        );
        $this->registerJavascript(
            'bootstrap',
            'modules/'.$this->module->name.'/views/js/bootstrap.js'
        );
        $this->registerJavascript(
            'angular',
            'modules/'.$this->module->name.'/views/js/angular1_6.js'
        );
        $this->registerJavascript(
            'vendor',
            'modules/'.$this->module->name.'/views/js/vendors.min.js'
        );
        $this->registerJavascript(
            'qrcode',
            'modules/'.$this->module->name.'/views/js/angular-qrcode.js'
        );
        $this->registerJavascript(
            'bciInvoice',
            'modules/'.$this->module->name.'/views/js/app.js'
        );
    }

    public function postProcess()
    {
        $cart = $this->context->cart;
        $blockchaininfobtc = $this->module;

        if (!isset($cart->id) || $cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$blockchaininfobtc->active) {
            Tools::redirect(__PS_BASE_URI__.'order.php?step=1');
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');
        }

        $currency = $this->context->currency;
        $total = (float)($cart->getOrderTotal(true, Cart::BOTH));

        // API Key not set
        if (!Configuration::get('BLOCKCHAININFOBTC_API_KEY')) {
            $error_str = $blockchaininfobtc->l('API Key not set. Please login to Admin and go to BlockchaininfoBTC module configuration to set you API Key.', 'validation');
            $this->displayError($error_str, $blockchaininfobtc);
        }

        $token = $this->generateToken();
        $addressObj = $blockchaininfobtc->getNewAddress($token, (int)($cart->id));

        $this->checkForErrors($addressObj, $blockchaininfobtc);

        $new_address = $addressObj->address;

        $current_time = time();
        $btcprice = $blockchaininfobtc->getBtcPrice($currency->id, $total);
        $btcrate = $blockchaininfobtc->getBtcRate($currency->id);

        if (!$btcprice) {
            Tools::redirect(__PS_BASE_URI__.'order.php?step=1');
        }

        $bits = (int)(1.0e8*$btcprice);

        $mailVars =  array(
            '{bitcoin_address}' => $new_address,
            '{bits}' => $btcprice,
            '{track_url}' => Tools::getHttpHost(true, true) . __PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.(int)($cart->id).'&id_module='.(int)($blockchaininfobtc->id).'&id_order='.$blockchaininfobtc->currentOrder.'&key='.$customer->secure_key
        );

        $mes = "Bitcoin Address: " .$new_address;
        $blockchaininfobtc->validateOrder((int)($cart->id), Configuration::get('BLOCKCHAININFOBTC_ORDER_STATE_WAIT'), $total, $blockchaininfobtc->displayName, $mes, $mailVars, (int)($currency->id), false, $customer->secure_key);

        Db::getInstance()->Execute(
            "INSERT INTO "._DB_PREFIX_."blockchaininfobtc (id_order, timestamp, addr, txid, status, value, bits, bits_payed) VALUES
            ('".(int)$blockchaininfobtc->currentOrder."','".(int)$current_time."','".pSQL($new_address)."', '', -1,'".(float)$total."','".(int)$bits."', 0)"
        );

        $redirect_link = '/store/index.php?controller=order-confirmation&id_cart='.(int)($cart->id).'&id_module='.(int)($blockchaininfobtc->id).'&id_order='.$blockchaininfobtc->currentOrder.'&key='.$customer->secure_key;
        /*
        $cancel_url = $this->context->link->getModuleLink(
            'blockchaininfobtc',
            'callback',
            array(
                'bci_token' => $token,
                'cart_id' => (int)($cart->id),
                'confirmations' => -1
            )
        ); */
        $cancel_url = '/store/module/blockchaininfobtc/callback?cart_id='.(int)($cart->id);

        $this->context->smarty->assign(
            array(
                'id_order' => (int)($blockchaininfobtc->currentOrder),
                'cart_id' => (int)($cart->id),
                'status' => -1,
                'addr' => $new_address,
                'txid' => "",
                'bits' => (float)$btcprice,
                'value' => (float)$total,
                'base_url' => Configuration::get('BLOCKCHAININFOBTC_BASE_URL'),
                'base_websocket_url' => Configuration::get('BLOCKCHAININFOBTC_WEBSOCKET_URL'),
                'bci_token' => $token,
                'timestamp' => $current_time,
                'currency_iso_code' => $currency->iso_code,
                'bitcoin_rate' => $btcrate,
                'bits_payed' => 0,
                'redirect_link' => $redirect_link,
                'cancel_url' => $cancel_url
            )
        );

        $this->setTemplate('module:blockchaininfobtc/views/templates/front/payment_confirm.tpl');
    }

    private function generateToken()
    {
        return hash('sha256', Configuration::get('BLOCKCHAININFOBTC_CALLBACK_SECRET'));
    }

    private function displayError($error_str, $blockchaininfobtc)
    {
        $unable_to_generate = '<h4>'.$blockchaininfobtc->l('Unable to generate bitcoin address.', 'validation').'</h4><p>'.$blockchaininfobtc->l('Note for site webmaster: ', 'validation');

        $troubleshooting_guide = '</p><p>'.$blockchaininfobtc->l('If problem persists, please consult ', 'validation').'<a href="https://ficus.myvnc.com/store/en/contact-us" target="_blank">'.$blockchaininfobtc->l('this troubleshooting article', 'validation').'</a></p>';

        $error_message = $unable_to_generate . $error_str . $troubleshooting_guide;

        echo $error_message;
        die();
    }

    private function checkForErrors($addressObj, $blockchaininfobtc)
    {
        if (!isset($addressObj->response_code)) {
            $error_str = $blockchaininfobtc->l('Your webhost is blocking outgoing HTTPS connections. BlockchaininfoBTC requires an outgoing HTTPS POST (port 443) to generate new address. Check with your webhosting provider to allow this.', 'validation');
        } else {
            switch ($addressObj->response_code) {
                case 200:
                    break;
                case 401:
                    $error_str = $blockchaininfobtc->l('API Key is incorrect. Make sure that the API key set in admin BlockchaininfoBTC module configuration is correct.', 'validation');
                    break;
                case 500:
                    if (isset($addressObj->message)) {
                        $error_code = $addressObj->message;
                        switch ($error_code) {
                            case "Could not find matching xpub":
                                $error_str = $blockchaininfobtc->l('There is a problem in the Callback URL. Make sure that you have set your Callback URL from the admin BlockchaininfoBTC module configuration to your Merchants > Settings.', 'validation');
                                break;
                            case "This require you to add an xpub in your wallet watcher":
                                $error_str = $blockchaininfobtc->l('There is a problem in the XPUB. Make sure that the you have added an address to Wallet Watcher > Address Watcher. If you have added an address make sure that it is an XPUB address and not a Bitcoin address.', 'validation');
                                break;
                            default:
                                $error_str = $addressObj->message;
                        }
                        break;
                    } else {
                        $error_str = $addressObj->response_code;
                        break;
                    }
                //no break here as if/else handles that
                default:
                    $error_str = 'HTTP Status code: '.$addressObj->response_code;
                    break;
            }
        }

        if (isset($error_str)) {
            $this->displayError($error_str, $blockchaininfobtc);
        }
    }
}
