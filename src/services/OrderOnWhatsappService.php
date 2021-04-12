<?php
/**
 * Order on Whatsapp plugin for Craft CMS 3.x
 *
 * Order on Whatsapp
 *
 * @link      https://www.zealousweb.com
 * @copyright Copyright (c) 2021 zealousweb
 */

namespace zealouswebcraftcms\orderonwhatsapp\services;

use zealouswebcraftcms\orderonwhatsapp\OrderOnWhatsapp;

use Craft;
use craft\base\Component;

/**
 * OrderOnWhatsappService Service
 *
 * All of your plugin’s business logic should go in services, including saving data,
 * retrieving data, etc. They provide APIs that your controllers, template variables,
 * and other plugins can interact with.
 *
 * https://craftcms.com/docs/plugins/services
 *
 * @author    zealousweb
 * @package   OrderOnWhatsapp
 * @since     1.0.0
 */
class OrderOnWhatsappService extends Component
{
    // Public Methods
    // =========================================================================

    /**
     * This function can literally be anything you want, and you can have as many service
     * functions as you want
     *
     * From any other plugin file, call it like this:
     *
     *     OrderOnWhatsapp::$plugin->orderOnWhatsappService->exampleService()
     *
     * @return mixed
     */
    public function exampleService()
    {
        $result = 'something';
        // Check our Plugin's settings for `someAttribute`
        if (OrderOnWhatsapp::$plugin->getSettings()->someAttribute) {
        }

        return $result;
    }
}
