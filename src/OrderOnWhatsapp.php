<?php
/**
 * Order on Whatsapp plugin for Craft CMS 3.x
 *
 * Order on Whatsapp
 *
 * @link      https://www.zealousweb.com
 * @copyright Copyright (c) 2021 zealousweb
 */

namespace zealouswebcraftcms\orderonwhatsapp;

use zealouswebcraftcms\orderonwhatsapp\services\OrderOnWhatsappService as OrderOnWhatsappServiceService;
use zealouswebcraftcms\orderonwhatsapp\variables\OrderOnWhatsappVariable;
use zealouswebcraftcms\orderonwhatsapp\models\Settings;
use zealouswebcraftcms\orderonwhatsapp\fields\OrderOnWhatsappField as OrderOnWhatsappFieldField;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\services\Elements;
use craft\services\Fields;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;
use craft\web\View;
use craft\helpers\UrlHelper;
use craft\commerce\elements\Product;
use craft\commerce\records\Product as ProductType;
use craft\commerce\records\Order as OrderRecords;
use craft\commerce\elements\Order;
use craft\commerce\Plugin as CommercePlugin;
use craft\commerce\models\Address;
use craft\commerce\models\Customer;
use craft\commerce\models\State; 
use craft\commerce\services\Countries;
use Yii;
use craft\events\TemplateEvent;

/**
 * Craft plugins are very much like little applications in and of themselves. We’ve made
 * it as simple as we can, but the training wheels are off. A little prior knowledge is
 * going to be required to write a plugin.
 *
 * For the purposes of the plugin docs, we’re going to assume that you know PHP and SQL,
 * as well as some semi-advanced concepts like object-oriented programming and PHP namespaces.
 *
 * https://docs.craftcms.com/v3/extend/
 *
 * @author    zealousweb
 * @package   OrderOnWhatsapp
 * @since     1.0.0
 *
 * @property  OrderOnWhatsappServiceService $orderOnWhatsappService
 * @property  Settings $settings
 * @method    Settings getSettings()
 */
class OrderOnWhatsapp extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * Static property that is an instance of this plugin class so that it can be accessed via
     * OrderOnWhatsapp::$plugin
     *
     * @var OrderOnWhatsapp
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * To execute your plugin’s migrations, you’ll need to increase its schema version.
     *
     * @var string
     */
    public $schemaVersion = '1.0.0';

    /**
     * Set to `true` if the plugin should have a settings view in the control panel.
     *
     * @var bool
     */
    public $hasCpSettings = true;

    /**
     * Set to `true` if the plugin should have its own section (main nav item) in the control panel.
     *
     * @var bool
     */
    public $hasCpSection = false;

    // Public Methods
    // =========================================================================

    /**
     * Set our $plugin static property to this class so that it can be accessed via
     * OrderOnWhatsapp::$plugin
     *
     * Called after the plugin class is instantiated; do any one-time initialization
     * here such as hooks and events.
     *
     * If you have a '/vendor/autoload.php' file, it will be loaded for you automatically;
     * you do not need to load it in your init() method.
     *
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        if (!Craft::$app->plugins->isPluginInstalled('commerce') && !Craft::$app->request->getIsConsoleRequest()) {
            Craft::$app->session->setNotice(Craft::t('order-on-whatsapp', 'Please install/activate the Craft  Commerce Plugin for the Order On WhatsApp Plugin to function.'));
        }

        /* Hook for Product list page ORDER ON WHATSAPP fuctionality */
        Craft::$app->view->hook('whatsapp-order-product-list', function(array &$context) {
            $query = Product::findOne(trim($context['productId']));
            if((isset($context['productImage'])) && $context['productImage'] != ""){
            $Image_url = $context['productImage'];
            }
            else {
                $context['productImage'] = "";
                $Image_url = "";
            }
            $title = $query['title'];
            $slug = $query['slug'];
            $qty = "1";
            $price = number_format($query['defaultPrice'], 2, '.', '') .' ('.$query['defaultCurrency'].')';
            $message = "*I’d like to enquire about the product and buy it*";
            $msg = $message."\n\n";
            if($Image_url){
             $msg .= '*Name:* '.$title."\n".'*Quantity:* '.$qty."\n".'*Price:* '.$price."\n".'*Image:* '.$Image_url."\n";
            }
            else{
             $msg .= '*Name:* '.$title."\n".'*Quantity:* '.$qty."\n".'*Price:* '.$price."\n";    
            }
            $settings = $this->getSettings();
            $whatsapp_number = $settings['countrycode'].''.$settings['whatsappnumber'];
            $html = '';
                
            $oldMode = \Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $html = \Craft::$app->view->renderTemplate('order-on-whatsapp/product_list', [
                'settings' => $settings,
                'number'  =>  $whatsapp_number,
                'message'  => $msg,
                'queryTypeId'  => $query->typeId
            ]);
            Craft::$app->view->setTemplateMode($oldMode);
            return $html;
        });

        /*Hook for Product detail page ORDER ON WHATSAPP and SHARE fuctionality */
        Craft::$app->view->hook('whatsapp-order-product-detail', function(array &$context ) {
            $query = Product::findOne(trim($context['productId']));
            if((isset($context['productImage'])) && $context['productImage'] != ""){
                $Image_url = $context['productImage'];
                }
                else{
                    $context['productImage'] = "";
                    $Image_url = "";
                }
            $title = $query['title'];
            $qty = "1";
            $price = number_format($query['defaultPrice'], 2, '.', '') .' ('.$query['defaultCurrency'].')';
           
            /* Get Current Page URL */
            $current_url = $_SERVER['REQUEST_URI'];
            $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $send_url = $protocol . $_SERVER['HTTP_HOST'] . $current_url;
            $message = "*I’d like to enquire about the product and buy it*";
            $msg = $message."\n\n";
            if($Image_url){
            $msg .= '*Name:* '.$title."\n".'*Quantity:* '.$qty."\n".'*Price:* '.$price."\n".'*Image:* '.$Image_url."\n";
            }
            else{
                $msg .= '*Name:* '.$title."\n".'*Quantity:* '.$qty."\n".'*Price:* '.$price."\n";    
            }
            
            /* Get setting page Data fron controll panel */
            $settings = $this->getSettings();
            $whatsapp_number = $settings['countrycode'].''.$settings['whatsappnumber'];
            $html = '';
            $oldMode = \Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $html =  \Craft::$app->view->renderTemplate('order-on-whatsapp/product_detail', [
                'settings' => $settings,
                'number' => $whatsapp_number,
                'message' => $msg,
                'queryTypeId'  => $query->typeId
            ]);
            Craft::$app->view->setTemplateMode($oldMode);
            return $html;
        });

        /* Hook for cart page ORDER ON WHATSAPP fuctionality */
        Craft::$app->view->hook('whatsapp-order-product-cart', function(array &$context, &$template) {
            $request = Craft::$app->getRequest();
            $data = array();
            $order = CommercePlugin::getInstance()->getCarts()->getCart();
            
            if($order) {
                $data = array();
                foreach ($order->getLineItems() as $key => $item) {
                    $data[$key]['title'] = $item['snapshot']['title'];
                    $data[$key]['qty'] = $item['qty'];
                    $data[$key]['price'] = number_format($item['qty']*$item['price'], 2, '.', '') .' ('.$order['defaultCurrency'].')';
                }
            }

            $total_price = number_format($order['storedTotalPrice'], 2, '.', '') .' ('.$order['defaultCurrency'].')'; 
            $message = "*I would like to buy the following product(s)*";
            $msg = $message."\n\n";
            foreach ($data as $k => $val) {
                $number = $k + 1;
                $msg .= '*Product '.$number.':* '.$val['title']."\n".'*Quantity:* '.$val['qty']."\n".'*Price:* '.$val['price']."\n";
                $msg .= "\n";
            }
            $msg .= '*Total Price:* '.$total_price;
            
            /* Get setting page Data fron controll panel */
            $settings = $this->getSettings();
            $whatsapp_number = $settings->countrycode.''.$settings->whatsappnumber;
            $html = '';
            
            if($settings["cartpage"] == 1) { 
                $oldMode = \Craft::$app->view->getTemplateMode();
                Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
                $html = \Craft::$app->view->renderTemplate('order-on-whatsapp/cart', [
                    'settings' => $settings,
                    'number' => $whatsapp_number,
                    'message' => $msg
                ]);
                Craft::$app->view->setTemplateMode($oldMode);
                return $html;
            } 
        });

        /* Hook for checkout page ORDER ON WHATSAPP fuctionality */
        Craft::$app->view->hook('whatsapp-order-checkout-button', function(array &   $context, &$template) {
            $oldMode = \Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $template = \Craft::$app->view->renderTemplate('order-on-whatsapp/checkoutJs');
            Craft::$app->view->setTemplateMode($oldMode);
            return $template;
        });

        /* Hook for Product list page SHARE ON WHATSAPP fuctionality */
        Craft::$app->view->hook('whatsapp-share-product-list', function(array &$context) {
            $query = Product::findOne(trim($context['productId']));
            if((isset($context['productImage'])) && $context['productImage'] != ""){
                $Image_url = $context['productImage'];
                }
                else{
                    $context['productImage'] = "";
                    $Image_url = "";
                }
            $title = $query['title'];
            $slug = $query['slug'];
            $qty = "1";
            $price = number_format($query['defaultPrice'], 2, '.', '') .' ('.$query['defaultCurrency'].')';
            $message = "*Check out this product*";
            $msg = $message."\n\n";
            if($Image_url){
                 $msg .= '*Name:* '.$title."\n".'*Quantity:* '.$qty."\n".'*Price:* '.$price."\n".'*Image:* '.$Image_url."\n";
                }
                else{
                 $msg .= '*Name:* '.$title."\n".'*Quantity:* '.$qty."\n".'*Price:* '.$price."\n";    
                }
            $settings = $this->getSettings();
            $whatsapp_number = $settings['countrycode'].''.$settings['whatsappnumber'];
            $html = '';
            
            $oldMode = \Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $html = \Craft::$app->view->renderTemplate('order-on-whatsapp/share_product_list', [
                'settings' => $settings,
                'number'  =>  $whatsapp_number,
                'message'  => $msg,
                'queryTypeId'  => $query->typeId
            ]);
            Craft::$app->view->setTemplateMode($oldMode);
            return $html;
        });

        /* Hook for Product detail page SHARE ON WHATSAPP fuctionality */
        Craft::$app->view->hook('whatsapp-share-product-detail', function(array &$context ) {
            $query = Product::findOne(trim($context['productId']));
            if((isset($context['productImage'])) && $context['productImage'] != ""){
                $Image_url = $context['productImage'];
                }
                else{
                    $context['productImage'] = "";
                    $Image_url = "";
                }
            $title = $query['title'];
            $qty = "1";
            $price = number_format($query['defaultPrice'], 2, '.', '') .' ('.$query['defaultCurrency'].')';
            
            /* Get Current Page URL */
            $current_url = $_SERVER['REQUEST_URI'];
            $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $send_url = $protocol . $_SERVER['HTTP_HOST'] . $current_url;
            $message = "*Check out this product*";
            $msg = $message."\n\n";
            if($Image_url){
                 $msg .= '*Name:* '.$title."\n".'*Quantity:* '.$qty."\n".'*Price:* '.$price."\n".'*Image:* '.$Image_url."\n";
                }
                else{
                 $msg .= '*Name:* '.$title."\n".'*Quantity:* '.$qty."\n".'*Price:* '.$price."\n";    
                }
               
            
            /* Get setting page Data fron controll panel */
            $settings = $this->getSettings();
            $whatsapp_number = $settings['countrycode'].''.$settings['whatsappnumber'];
            $html = '';
                
            $oldMode = \Craft::$app->view->getTemplateMode();
            Craft::$app->view->setTemplateMode(View::TEMPLATE_MODE_CP);
            $html =  \Craft::$app->view->renderTemplate('order-on-whatsapp/share_product_detail', [
                'settings' => $settings,
                'number' => $whatsapp_number,
                'message' => $msg,
                'queryTypeId'  => $query->typeId
            ]);
            Craft::$app->view->setTemplateMode($oldMode);
            return $html;
        });

        // Register our site routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['siteActionTrigger1'] = 'order-on-whatsapp/default';
            }
        );

        // Register our CP routes
        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules['cpActionTrigger1'] = 'order-on-whatsapp/default/do-something';
            }
        );

        // Register our elements
        Event::on(
            Elements::class,
            Elements::EVENT_REGISTER_ELEMENT_TYPES,
            function (RegisterComponentTypesEvent $event) {
            }
        );

        // Register our fields
        Event::on(
            Fields::class,
            Fields::EVENT_REGISTER_FIELD_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = OrderOnWhatsappFieldField::class;
            }
        );

        // Register our variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                /** @var CraftVariable $variable */
                $variable = $event->sender;
                $variable->set('orderOnWhatsapp', OrderOnWhatsappVariable::class);
            }
        );

        // Do something after we're installed
        Event::on(
            Plugins::class,
            Plugins::EVENT_AFTER_INSTALL_PLUGIN,
            function (PluginEvent $event) {
                if ($event->plugin === $this) {
                    // We were just installed
                }
            }
        );
        Event::on(View::class, View::EVENT_BEFORE_RENDER_TEMPLATE, function (TemplateEvent $e) {
            if (
                $e->template === 'settings/plugins/_settings' &&
                $e->variables['plugin'] === $this
            ) {
                // Add the tabs in setting Page
                $e->variables['tabs'] = [
                    ['label' => 'Settings', 'url' => '#settings-tab-settings'],
                    ['label' => 'WhatsApp Button', 'url' => '#settings-tab-whatsapp-button'],
                    ['label' => 'Share Button', 'url' => '#settings-tab-share-button'],
                ];
            }
        });

/**
 * Logging in Craft involves using one of the following methods:
 *
 * Craft::trace(): record a message to trace how a piece of code runs. This is mainly for development use.
 * Craft::info(): record a message that conveys some useful information.
 * Craft::warning(): record a warning message that indicates something unexpected has happened.
 * Craft::error(): record a fatal error that should be investigated as soon as possible.
 *
 * Unless `devMode` is on, only Craft::warning() & Craft::error() will log to `craft/storage/logs/web.log`
 *
 * It's recommended that you pass in the magic constant `__METHOD__` as the second parameter, which sets
 * the category to the method (prefixed with the fully qualified class name) where the constant appears.
 *
 * To enable the Yii debug toolbar, go to your user account in the AdminCP and check the
 * [] Show the debug toolbar on the front end & [] Show the debug toolbar on the Control Panel
 *
 * http://www.yiiframework.com/doc-2.0/guide-runtime-logging.html
 */
        Craft::info(
            Craft::t(
                'order-on-whatsapp',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * Creates and returns the model used to store the plugin’s settings.
     *
     * @return \craft\base\Model|null
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * Returns the rendered settings HTML, which will be inserted into the content
     * block on the settings page.
     *
     * @return string The rendered settings HTML
     */
    protected function settingsHtml(): string
    {
        $countries = $this->Country();
        $settings = $this->getSettings();
        $request = Craft::$app->getRequest();
        if($request->isPost){
            if ($settings->validate()) {
                return Craft::$app->view->renderTemplate(
                    'order-on-whatsapp/settings',
                    [
                        'settings' => $settings,
                        'countries' => $countries,
                    ]
                );
            }
        } 
        return Craft::$app->view->renderTemplate(
            'order-on-whatsapp/settings',
            [
                'settings' => $settings,
                'countries' => $countries,
            ]
        );      
    }

    protected function Country()
    {
        $countries = Craft::$app->view->renderTemplate(
            'order-on-whatsapp/country_code');
        return json_decode($countries);
    }

   /*  After save redirect on setting page first tab */
    public function afterSaveSettings()
    {
        parent::afterSaveSettings();
        Craft::$app->response
            ->redirect(UrlHelper::url('settings/plugins/order-on-whatsapp#settings-tab-settings'))
            ->send();
    }
}
