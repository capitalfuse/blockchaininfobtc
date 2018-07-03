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

use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Blockchaininfobtc extends PaymentModule
{
    private $_html = '';
    private $_postErrors = array();

    public function __construct()
    {
        $this->name = 'blockchaininfobtc';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.1';
        $this->author = 'Takanobu Fuse';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
        $this->controllers = array('validation', 'callback');

        parent::__construct();

        $this->displayName = $this->l('Blockchain.info BTC Payment Module');
        $this->description = $this->l('Payment module for Blockchain.info BTC');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall this module?');

        //Include configuration from the local file.
<<<<<<< HEAD
        $BLOCKCHAININFOBTC_BASE_URL = 'https://www.blockchain.com';
=======
        $BLOCKCHAININFOBTC_BASE_URL = 'https://api.blockchain.info';
>>>>>>> 1609f11b694dcacb8508a9d3843b19cd9f657b95
        $BLOCKCHAININFOBTC_WEBSOCKET_URL = 'wss://ws.blockchain.info/inv';
        $BLOCKCHAININFOBTC_NEW_ADDRESS_URL = 'https://api.blockchain.info/v2/receive';
        $BLOCKCHAININFOBTC_PRICE_URL = 'https://blockchain.info/tobtc?currency=';
        $BLOCKCHAININFOBTC_RATE_URL = 'https://blockchain.info/ticker';

        Configuration::updateValue('BLOCKCHAININFOBTC_BASE_URL', $BLOCKCHAININFOBTC_BASE_URL);
        Configuration::updateValue('BLOCKCHAININFOBTC_PRICE_URL', $BLOCKCHAININFOBTC_PRICE_URL);
        Configuration::updateValue('BLOCKCHAININFOBTC_NEW_ADDRESS_URL', $BLOCKCHAININFOBTC_NEW_ADDRESS_URL);
        Configuration::updateValue('BLOCKCHAININFOBTC_WEBSOCKET_URL', $BLOCKCHAININFOBTC_WEBSOCKET_URL);
        Configuration::updateValue('BLOCKCHAININFOBTC_RATE_URL', $BLOCKCHAININFOBTC_RATE_URL);

        if (!Configuration::get('BLOCKCHAININFOBTC_API_KEY')) {
            $this->warning = $this->l('API Key is not provided to communicate with Blockchain.info');
        }

        if (!Configuration::get('BLOCKCHAININFOBTC_XPUB_ADDR')) {
            $this->warning = $this->l('xPub is not provided to communicate with Blockchain.info');
        }
    }

    /**
    * Don't forget to create update methods if needed:
    * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
    */
    public function install()
    {
        if (!function_exists('curl_version')) {
            $this->_errors[] = $this->l('Sorry, this module requires the cURL PHP extension but it is not enabled on your server.  Please ask your web hosting provider for assistance.');
            return false;
        }

        if (!parent::install()
        || !$this->installOrder('BLOCKCHAININFOBTC_ORDER_STATE_WAIT', 'Awaiting Bitcoin Payment', null)
        || !$this->installOrder('BLOCKCHAININFOBTC_ORDER_STATUS_0', 'Waiting for 3 Confirmations', null)
        || !$this->installOrder('BLOCKCHAININFOBTC_ORDER_STATUS_3', 'Bitcoin Payment Confirmed', null)
        || !$this->installDB()
        || !$this->registerHook('paymentOptions')
        || !$this->registerHook('paymentReturn')
        || !$this->registerHook('displayInvoice')
<<<<<<< HEAD
        || !$this->registerHook('displayOrderDetail')
=======
        || !$this->registerHook('displayPDFInvoice')
>>>>>>> 1609f11b694dcacb8508a9d3843b19cd9f657b95
        ) {
            return false;
        }
        $this->active = true;
        return true;
    }

    public function uninstall()
    {
        if (!parent::uninstall()
        || !$this->uninstallOrder('BLOCKCHAININFOBTC_ORDER_STATE_WAIT')
        || !$this->uninstallOrder('BLOCKCHAININFOBTC_ORDER_STATUS_0')
        || !$this->uninstallOrder('BLOCKCHAININFOBTC_ORDER_STATUS_3')
        || !$this->uninstallDB()
        ) {
            return false;
        }
        return true;
    }

    public function installOrder($key, $title, $template)
    {
        //Already existing from previous install(ignore)
        if (Configuration::get($key)>0) {
            return true;
        }
        $orderState = new OrderState();
        $orderState->name = array_fill(0, 10, $title);
        $orderState->color = '#d7c500';
        $orderState->send_email = isset($template);
        $orderState->template = array_fill(0, 10, $template);
        $orderState->hidden = false;
        $orderState->delivery = false;
        $orderState->logable = false;
        $orderState->invoice = false;

        if (!$orderState->add()) {
            return false;
        }

        Configuration::updateValue($key, (int) $orderState->id);
        return true;
    }

    public function uninstallOrder($key)
    {
        $orderState = new OrderState();
        $orderState->id = (int) Configuration::get($key);
        $orderState->delete();
        Configuration::deleteByName($key);

        return true;
    }

    public function installDB()
    {
        $db = Db::getInstance();

        $query = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'blockchaininfobtc` (
            `id_blockchaininfobtc` int(11) NOT NULL AUTO_INCREMENT,
            `id_order` INT UNSIGNED NOT NULL,
            `timestamp` INT(8) NOT NULL,
            `addr` varchar(255) NOT NULL,
            `txid` varchar(255) NOT NULL,
            `status` int(8) NOT NULL,
            `value` double(10,2) NOT NULL,
            `bits` int(8) NOT NULL,
            `bits_payed` int(8) NOT NULL,
            PRIMARY KEY  (`id_blockchaininfobtc`),
            UNIQUE KEY `order_table` (`addr`)
            ) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

            $db->Execute($query);

            //Blockchain.info basic configuration
            Configuration::updateValue('BLOCKCHAININFOBTC_API_KEY', '');

            //Send address generated xpub on blockchain.info.
            Configuration::updateValue('BLOCKCHAININFOBTC_XPUB_ADDR', '');

            //Generate callback secret
            $secret = md5(uniqid(rand(), true));
            Configuration::updateValue('BLOCKCHAININFOBTC_CALLBACK_SECRET', $secret);

            return true;
    }

    public function uninstallDB()
    {
        Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'blockchaininfobtc`;');
        Configuration::deleteByName('BLOCKCHAININFOBTC_API_KEY');
        Configuration::deleteByName('BLOCKCHAININFOBTC_XPUB_ADDR');
        Configuration::deleteByName('BLOCKCHAININFOBTC_CALLBACK_SECRET');
        Configuration::deleteByName('BLOCKCHAININFOBTC_BASE_URL');
        Configuration::deleteByName('BLOCKCHAININFOBTC_PRICE_URL');
        Configuration::deleteByName('BLOCKCHAININFOBTC_NEW_ADDRESS_URL');
        Configuration::deleteByName('BLOCKCHAININFOBTC_WEBSOCKET_URL');
        return true;
    }

    public function getBtcPrice($id_currency, $total_price)
    {
        //Generate total price to BTC price
        $currency = new Currency((int) $id_currency);
        $options = array( 'http' => array( 'method'  => 'GET') );
        $context = stream_context_create($options);
        $totalprice = (float)$total_price;
        $btcprice = Tools::file_get_contents(Configuration::get('BLOCKCHAININFOBTC_PRICE_URL').$currency->iso_code .'&value='. $totalprice, false, $context);
        return $btcprice;
    }

    public function getBtcRate($id_currency)
    {
        //Extract BTC rate at currency code
        $currency = new Currency((int) $id_currency);
        $currencycode = $currency->iso_code;
        $options = array( 'http' => array( 'method'  => 'GET') );
        $context = stream_context_create($options);
        $btcratejson = Tools::file_get_contents(Configuration::get('BLOCKCHAININFOBTC_RATE_URL'), false, $context);
        $btcrate = Tools::jsonDecode($btcratejson);
        return $btcrate->$currencycode->last;
    }

    public function getNewAddress($token, $cartid)
    {
        $my_xpub = Configuration::get('BLOCKCHAININFOBTC_XPUB_ADDR');
        $my_api_key = Configuration::get('BLOCKCHAININFOBTC_API_KEY');
        $my_callback_url = urlencode($this->context->link->getModuleLink(
            $this->name,
            'callback',
            array('bci_token' => $token,
                  'cart_id' => $cartid
            )
        ));
        $root_url = Configuration::get('BLOCKCHAININFOBTC_NEW_ADDRESS_URL');

        // $options = array( 'http' => array( 'method'  => 'GET') );
        // $context = stream_context_create($options);
        // $contents = Tools::file_get_contents($root_url. '?xpub=' .$my_xpub. '&callback=' .$my_callback_url. '&key=' .$my_api_key. '&gap_limit=100', false, $context);
        // $addressObj = Tools::jsonDecode($contents);
        // return $addressObj->address;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $root_url. '?xpub=' .$my_xpub. '&callback=' .$my_callback_url. '&key=' .$my_api_key. '&gap_limit=100');
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        //curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $data = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $addressObj = Tools::jsonDecode($data);
        if (!isset($addressObj)) {
            $addressObj = new stdClass();
        }
        $addressObj->{'response_code'} = $httpcode;

        return $addressObj;
    }

    /**
    * Load the configuration form
    */
    public function getContent()
    {
        if (Tools::isSubmit('btnSubmit') || Tools::isSubmit('btnUpdate')) {
            $this->_postValidation();
            if (!count($this->_postErrors)) {
                $this->_postProcess();
            } else {
                foreach ($this->_postErrors as $err) {
                    $this->_html .= $this->displayError($err);
                }
            }
        } else {
            $this->_html .= '<br />';
        }

        $this->_html .= $this->renderForm();

        return $this->_html;
    }

    protected function _postValidation()
    {
        if (Tools::isSubmit('btnSubmit')) {
            if (!Tools::getValue('BLOCKCHAININFOBTC_API_KEY')) {
                $this->_postErrors[] = $this->l('API Key is required.');
            } elseif (!Tools::getValue('BLOCKCHAININFOBTC_XPUB_ADDR')) {
                $this->_postErrors[] = $this->l('xPub Address is required.');
            }
        }
    }

    protected function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            Configuration::updateValue('BLOCKCHAININFOBTC_API_KEY', Tools::getValue('BLOCKCHAININFOBTC_API_KEY'));
            Configuration::updateValue('BLOCKCHAININFOBTC_XPUB_ADDR', Tools::getValue('BLOCKCHAININFOBTC_XPUB_ADDR'));
            $secret = md5(uniqid(rand(), true));
            Configuration::updateValue('BLOCKCHAININFOBTC_CALLBACK_SECRET', $secret);
        }
        $this->_html .= $this->displayConfirmation($this->l('Settings updated'));
    }

    public function renderForm()
    {
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Blockchaininfo Settings'),
                    'icon' => 'icon-envelope'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('API Key'),
                        'name' => 'BLOCKCHAININFOBTC_API_KEY',
                        'required' => true
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('xPub Address'),
                        'name' => 'BLOCKCHAININFOBTC_XPUB_ADDR',
                        'required' => true
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                )
            ),
        );

        $fields_additional_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Blockchaininfo Callback Secret'),
                    'icon' => 'icon-envelope'
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Callback Secret'),
                        'name' => 'BLOCKCHAININFOBTC_CALLBACK_SECRET',
                        'required' => true
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Update'),
                )
            ),
        );

        $helper = new HelperForm();
        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $lang = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? : 0;
        $this->fields_form = array();
        $helper->id = (int)Tools::getValue('id_carrier');
        $helper->identifier = $this->identifier;
        $helper->submit_action = 'btnSubmit';
        //$helper->submit_action = 'btnUpdate';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false).'&configure='
        .$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFieldsValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );

        return $helper->generateForm(array($fields_form, $fields_additional_form));
    }

    public function getConfigFieldsValues()
    {
        return array(
            'BLOCKCHAININFOBTC_API_KEY' => Tools::getValue('BLOCKCHAININFOBTC_API_KEY', Configuration::get('BLOCKCHAININFOBTC_API_KEY')),
            'BLOCKCHAININFOBTC_XPUB_ADDR' => Tools::getValue('BLOCKCHAININFOBTC_XPUB_ADDR', Configuration::get('BLOCKCHAININFOBTC_XPUB_ADDR')),
            'BLOCKCHAININFOBTC_CALLBACK_SECRET' => Tools::getValue('BLOCKCHAININFOBTC_CALLBACK_SECRET', Configuration::get('BLOCKCHAININFOBTC_CALLBACK_SECRET')),
        );
    }

    /**
    * This method is used to render the payment button,
    * Take care if the button should be displayed or not.
    */
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }

        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $payment_options = array($this->getBitcoinPaymentOption());
        return $payment_options;
    }

    public function checkCurrency($cart)
    {
        $currency_order = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);

        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getBitcoinPaymentOption()
    {
        $bitcoinOption = new PaymentOption();
        $bitcoinOption->setCallToActionText($this->l('Payment by Bitcoin'))
        ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
        ->setAdditionalInformation($this->context->smarty->fetch('module:blockchaininfobtc/views/templates/front/payment_infos.tpl'))
        ->setLogo(Media::getMediaPath(_PS_MODULE_DIR_.$this->name.'/logo.png'));
        return $bitcoinOption;
    }

    /**
<<<<<<< HEAD
    * This hook is used to display the order confirmation page. $params['order']->id (primary key = id_order).
=======
    * This hook is used to display the order confirmation page.
>>>>>>> 1609f11b694dcacb8508a9d3843b19cd9f657b95
    */
    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }

        if ($params['order']->getCurrentOrderState()->id != Configuration::get('PS_OS_ERROR')) {
            $this->smarty->assign('status', 'ok');
        }

<<<<<<< HEAD
        $b_order = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'blockchaininfobtc WHERE `id_order` = ' . (int)$params['order']->id. '  LIMIT 1');
=======
        $b_order = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'blockchaininfobtc WHERE `id_order` = ' . (int)$params['order']. '  LIMIT 1');
>>>>>>> 1609f11b694dcacb8508a9d3843b19cd9f657b95

        if ($b_order) {
            $tx_status = (int)($b_order[0]['status']);

            if ($tx_status == -1) {
                $bcstatus = 'Payment Not Received.';
            } elseif ($tx_status == 0) {
                $bcstatus = 'Waiting for 3 Confirmations.';
            } else {
                $bcstatus = 'Payment Confirmed.';
            }
            $this->context->smarty->assign(
                array(
                    'payment_options' => 'Payment by Bitcoin',
                    'id_order' => $params['order']->id,
                    'reference' => $params['order']->reference,
                    'total' => Tools::displayPrice($params['order']->getOrdersTotalPaid(), new Currency($params['order']->id_currency), false),
                    'bcstatus' => $bcstatus,
                    'addr' => $b_order[0]['addr'],
                    'txid' => $b_order[0]['txid'],
                    'bits' => $b_order[0]['bits'],
                    'base_url' => Configuration::get('BLOCKCHAININFOBTC_BASE_URL'),
                    'bits_payed' => $b_order[0]['bits_payed']
                )
            );
            return $this->fetch('module:blockchaininfobtc/views/templates/hook/payment_return.tpl');
        }
    }

    public function hookDisplayInvoice($params)
    {
<<<<<<< HEAD
        $b_order = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'blockchaininfobtc WHERE `id_order` = ' .(int)$params['id_order']. '  LIMIT 1');
=======
        $b_order = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'blockchaininfobtc WHERE `id_order` = ' . (int)$params['order']. '  LIMIT 1');

        /*
        print_r($b_order);
        */
>>>>>>> 1609f11b694dcacb8508a9d3843b19cd9f657b95

        if ($b_order) {
            $tx_status = (int)($b_order[0]['status']);

            if ($tx_status == -1) {
                $status = 'Payment Not Received.';
<<<<<<< HEAD
            } elseif ($tx_status >= 0 && $tx_status <= 3) {
=======
            } elseif ($tx_status == 0) {
>>>>>>> 1609f11b694dcacb8508a9d3843b19cd9f657b95
                $status = 'Waiting for 3 Confirmations.';
            } else {
                $status = 'Payment Confirmed.';
            }
            $this->context->smarty->assign(
                array(
                    'status' => $status,
                    'addr' => $b_order[0]['addr'],
                    'txid' => $b_order[0]['txid'],
                    'bits' => $b_order[0]['bits'],
                    'base_url' => Configuration::get('BLOCKCHAININFOBTC_BASE_URL'),
                    'bits_payed' => $b_order[0]['bits_payed']
                )
            );
            return $this->display(__FILE__, 'views/templates/hook/invoice.tpl');
        }
    }

<<<<<<< HEAD
    //Add Order detail info to customer page. $params['order']->id (primary key = id_order).
    public function hookDisplayOrderDetail($params)
    {
        $b_order = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'blockchaininfobtc WHERE `id_order` = ' .(int)$params['order']->id. '  LIMIT 1');
=======
    //Add Bitcoin invoice to pdf invoice
    public function hookDisplayPDFInvoice($params)
    {
        if (!$this->active) {
            return;
        }

        $b_order = Db::getInstance()->ExecuteS('SELECT * FROM ' . _DB_PREFIX_ . 'blockchaininfobtc WHERE `id_order` = ' .(int)$params['order']. '  LIMIT 1');
>>>>>>> 1609f11b694dcacb8508a9d3843b19cd9f657b95

        if ($b_order) {
            $this->context->smarty->assign(
                array(
<<<<<<< HEAD
                    'addr' => $b_order[0]['addr'],
                    'txid' => $b_order[0]['txid'],
                    'bits' => $b_order[0]['bits'],
=======
                    'status' => (int)($b_order[0]['status']),
                    'addr' => $b_order[0]['addr'],
                    'txid' => $b_order[0]['txid'],
>>>>>>> 1609f11b694dcacb8508a9d3843b19cd9f657b95
                    'base_url' => Configuration::get('BLOCKCHAININFOBTC_BASE_URL'),
                    'bits_payed' => $b_order[0]['bits_payed']
                )
            );

<<<<<<< HEAD
            return $this->display(__FILE__, 'views/templates/hook/order_details.tpl');
=======
            return $this->display(__FILE__, 'views/templates/hook/invoice_pdf.tpl');
>>>>>>> 1609f11b694dcacb8508a9d3843b19cd9f657b95
        }
    }
}
