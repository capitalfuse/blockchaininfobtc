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

class BlockchaininfobtcCallbackModuleFrontController extends ModuleFrontController
{
    public $ssl = true;

    public function postProcess()
    {
        $secret = Tools::getValue('bci_token');
        $secret = empty($secret) ? null : $secret;

        $status = Tools::getValue('confirmations');
        $status = empty($status) ? -1 : $status;

        $txid = Tools::getValue('transaction_hash');
        $txid = empty($txid) ? null : $txid;

        $satoshi = Tools::getValue('value');
        $satoshi = empty($satoshi) ? null : $satoshi;

        $token = $this->generateToken();
        $order_id = Order::getOrderByCartId((int)(Tools::getValue('cart_id')));
        $bci_order = Db::getInstance()->executeS("SELECT * FROM "._DB_PREFIX_."blockchaininfobtc WHERE `id_order` = '".(int)$order_id."' LIMIT 1");
        $order = new Order($bci_order[0]['id_order']);

        try {
            if ($secret == $token) {
                //Update order status
                $query="UPDATE "._DB_PREFIX_."blockchaininfobtc SET status='".(int)$status."',txid='".pSQL($txid)."',bits_payed=".(int)$satoshi." WHERE id_order='".(int)$order_id."'";
                // $result = Db::getInstance()->execute($query);
                Db::getInstance()->execute($query);

                if (!$order) {
                    $error_message = 'BlockchaininfoBTC Order #' . Tools::getValue('cart_id') . ' does not exists';
                    $this->logError($error_message, (int)(Tools::getValue('cart_id')));
                    throw new Exception($error_message);
                }

                if ($bci_order[0]['bits'] <= $bci_order[0]['bits_payed']) {
                    if ($status >= 0 && $status <= 2) {
                        $order->setCurrentState(Configuration::get('BLOCKCHAININFOBTC_ORDER_STATUS_0'));
                    } elseif ($status == 3) {
                        $order->setCurrentState(Configuration::get('BLOCKCHAININFOBTC_ORDER_STATUS_3'));
                    } elseif ($status >= 4) {
                        $order->setCurrentState(Configuration::get('PS_OS_PAYMENT'));
                        echo "*ok*";
                    }
                    exit();
                } else {
                    $order->setCurrentState(Configuration::get('PS_OS_ERROR'));
                    $this->context->smarty->assign(
                        array(
                            'title' => $this->l('Not match payment value with the request amount'),
                            'text' => $this->l('Please reconfirm your payment value, or please contact our customer support')
                              //if no ok message, the callback will be resent again for every new block (approximately every 10 minutes) up to 1000 times.
                        )
                    );
                }
            } elseif ($order->getCurrentState() == Configuration::get('BLOCKCHAININFOBTC_ORDER_STATE_WAIT')) {
                      $order->setCurrentState(Configuration::get('PS_OS_CANCELED'));
                      $this->context->smarty->assign(
                          array(
                              'title' => $this->l('Your Order is cancelled.'),
                              'text' => $this->l('Please confirm your e-mail for this cancellation.')
                          )
                      );
            } else {
                exit();
            }
        } catch (Exception $e) {
            $this->context->smarty->assign(
                array(
                    'text' => get_class($e) . ': ' . $e->getMessage()
                )
            );
        }
        $this->setTemplate('module:blockchaininfobtc/views/templates/front/payment_callback.tpl');
    }
    private function generateToken()
    {
        return hash('sha256', Configuration::get('BLOCKCHAININFOBTC_CALLBACK_SECRET'));
    }
    private function logError($message, $cart_id)
    {
        PrestaShopLogger::addLog($message, 3, null, 'Cart', $cart_id, true);
    }
}
