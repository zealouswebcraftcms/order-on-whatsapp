<?php
/**
 * Order on Whatsapp plugin for Craft CMS 3.x
 *
 * Order on Whatsapp
 *
 * @link      https://www.zealousweb.com
 * @copyright Copyright (c) 2021 zealousweb
 */

namespace zealouswebcraftcms\orderonwhatsapp\controllers;

use zealouswebcraftcms\orderonwhatsapp\OrderOnWhatsapp;

use Craft;
use craft\web\Controller;
use craft\commerce\Plugin as CommercePlugin;
use craft\commerce\elements\Product;
use \craft\commerce\elements\Order;

/**
 * Default Controller
 *
 * Generally speaking, controllers are the middlemen between the front end of
 * the CP/website and your plugin’s services. They contain action methods which
 * handle individual tasks.
 *
 * A common pattern used throughout Craft involves a controller action gathering
 * post data, saving it on a model, passing the model off to a service, and then
 * responding to the request appropriately depending on the service method’s response.
 *
 * Action methods begin with the prefix “action”, followed by a description of what
 * the method does (for example, actionSaveIngredient()).
 *
 * https://craftcms.com/docs/plugins/controllers
 *
 * @author    zealousweb
 * @package   OrderOnWhatsapp
 * @since     1.0.0
 */
class DefaultController extends Controller
{

    // Protected Properties
    // =========================================================================

    /**
     * @var    bool|array Allows anonymous access to this controller's actions.
     *         The actions must be in 'kebab-case'
     * @access protected
     */
    protected $allowAnonymous = true;

    // Public Methods
    // =========================================================================

    public function actionCheckoutPage() {
        $order = CommercePlugin::getInstance()->getCarts()->getCart();
        if($order)
        {
            $data = array();
            foreach ($order->getLineItems() as $key => $item) {
                $data[$key]['title'] = $item['snapshot']['title'];
                $data[$key]['qty'] = $item['qty'];
                $data[$key]['price'] = number_format($item['qty']*$item['price'], 2, '.', '') .' ('.$item['defaultCurrency'].')';
            }
        }
        $total_price = $order['storedTotalPrice'];        
        $msg = '*Cart Details:*'."\n\n";
        foreach ($data as $k => $val) {
            $number = $k + 1;
            $msg .= '*Product '.$number.':* '.$val['title']."\n".'*Quantity:* '.$val['qty']."\n".'*Price:* '.$val['price']."\n";
            $msg .= "\n";
        }
        $currency_type = $order['defaultCurrency'];
        $msg .= '*Order Total Price:* '.number_format($total_price, 2, '.', '').'('.$currency_type.')'."\n";
        $msg .= "\n";
        $data = array();
        $shipping_address = '';
        $billing_address = '';
        $shipping_same_billing_address = '';

        if(($order['shippingAddressId'] != '' && isset($order['shippingAddressId']) && isset($order['shippingAddressId']) != null) && ($order['billingAddressId'] != '' && isset($order['billingAddressId']) && isset($order['billingAddressId']) != null) && $order['shippingAddressId'] == $order['billingAddressId']) {
           
            $shipping_same_billing_address = CommercePlugin::getInstance()->getCustomers()->getCustomer()->getAddressById($order['billingAddressId']);
            $msg .= '*Shipping & Billing Details:* '."\n\n";
            $msg .= '*Name:* '.$shipping_same_billing_address['fullName']."\n";
            
            $msg .= '*Street Address:* '.$shipping_same_billing_address['address1'];
            if(isset($shipping_same_billing_address['address2']) && $shipping_same_billing_address['address2'] != '') {
                $msg .=', '.$shipping_same_billing_address['address2'];
            }
            if(isset($shipping_same_billing_address['address3']) && $shipping_same_billing_address['address3'] != '') {
                $msg.=', '.$shipping_same_billing_address['address3'];  
            }
            $msg .= "\n".'*Postcode/ZIP:* '.$shipping_same_billing_address['zipCode']."\n";
            $msg .= '*Town/City:* '.$shipping_same_billing_address['city']."\n";
            $state = CommercePlugin::getInstance()->getStates()->getStateById($shipping_same_billing_address['stateId']);
            $msg .= '*State/Country:* '.$state."\n";
            $country = CommercePlugin::getInstance()->getCountries()->getCountryById($shipping_same_billing_address['countryId']);
            $msg .= '*Country:* '.$country."\n";
            if( isset($shipping_same_billing_address['phone']) && $shipping_same_billing_address['phone'] != '' && isset($shipping_same_billing_address['alternativePhone']) && $shipping_same_billing_address['alternativePhone']  != '')
            {
                $msg .= '*Phone:* '.$shipping_same_billing_address['phone'].', '.$shipping_same_billing_address['alternativePhone']."\n\n";
            } else if ( isset($shipping_same_billing_address['phone']) && $shipping_same_billing_address['phone'] != '') {
                $msg .= '*Phone:* '.$shipping_same_billing_address['phone'];
            } else {
                $msg .= '*Phone:* '.$shipping_same_billing_address['alternativePhone'];
            }
             
        } else {

            if($order['shippingAddressId'] != '' && isset($order['shippingAddressId']) && isset($order['shippingAddressId']) != null) {
                $shipping_address = CommercePlugin::getInstance()->getCustomers()->getCustomer()->getAddressById($order['shippingAddressId']);
                $msg .= '*Shipping Details:* '."\n\n";
                $msg .= '*Name:* '.$shipping_address['fullName']."\n";
                $msg .= '*Street Address:* '.$shipping_address['address1'];
                if(isset($shipping_address['address2']) && $shipping_address['address2'] != '') {
                    $msg .= ', '.$shipping_address['address2'];
                } if(isset($shipping_address['address3']) && $shipping_address['address3'] != '') {
                    $msg.= ', '.$shipping_address['address3'];  
                }
                $msg .= "\n".'*Town/City:* '.$shipping_address['city']."\n";
                $msg .= '*Postcode/ZIP:* '.$shipping_address['zipCode']."\n";
                $state = CommercePlugin::getInstance()->getStates()->getStateById($shipping_address['stateId']);
                $msg .= '*State/Country:* '.$state."\n";
                $country = CommercePlugin::getInstance()->getCountries()->getCountryById($shipping_address['countryId']);
                $msg .= '*Country:* '.$country."\n";
                if( isset($shipping_address['phone']) && $shipping_address['phone'] != '' && isset($shipping_address['alternativePhone']) && $shipping_address['alternativePhone']  != '')
                {
                    $msg .= '*Phone:* '.$shipping_address['phone'].', '.$shipping_address['alternativePhone']."\n\n";
                } else if ( isset($shipping_address['phone']) && $shipping_address['phone'] != '' && $shipping_address['alternativePhone']  == '') {
                    $msg .= '*Phone:* '.$shipping_address['phone']."\n";
                } else {
                    $msg .= '*Phone:* '.$shipping_address['alternativePhone'];
                }
            } 

            if($order['billingAddressId'] != '' && isset($order['billingAddressId']) && isset($order['billingAddressId']) != null) {
                $billing_address = CommercePlugin::getInstance()->getCustomers()->getCustomer()->getAddressById($order['billingAddressId']);
                $msg .= '*Billing Details:* '."\n\n";
                $msg .= '*Name:* '.$billing_address['fullName']."\n";
                $msg .= '*Street Address:* '.$billing_address['address1'];
                if(isset($billing_address['address2']) && $billing_address['address2'] != '') {
                    $msg .= ', '.$billing_address['address2'];
                } if(isset($billing_address['address3']) && $billing_address['address3'] != '') {
                    $msg.= ', '.$billing_address['address3'];  
                }
                $msg .= "\n".'*Town/City:* '.$billing_address['city']."\n";
                $msg .= '*Postcode/ZIP:* '.$billing_address['zipCode']."\n";
                $state = CommercePlugin::getInstance()->getStates()->getStateById($billing_address['stateId']);
                $msg .= '*State/Country:* '.$state."\n";
                $country = CommercePlugin::getInstance()->getCountries()->getCountryById($billing_address['countryId']);
                $msg .= '*Country:* '.$country."\n";
                if( isset($billing_address['phone']) && $billing_address['phone'] != '' && isset($billing_address['alternativePhone']) &&  $billing_address['alternativePhone']  != '')
                {
                    $msg .= '*Phone:* '.$billing_address['phone'].', '.$billing_address['alternativePhone']."\n\n";
                } else if ( isset($billing_address['phone']) && $billing_address['phone'] != '' && $billing_address['alternativePhone']  == '') {
                    $msg .= '*Phone:* '.$billing_address['phone']."\n";
                } else {
                    $msg .= '*Phone:* '.$billing_address['alternativePhone'];
                }
            } 
        }
        return json_encode($msg);
    }
}
