<?php
/**
 * Order on Whatsapp plugin for Craft CMS 3.x
 *
 * Order on Whatsapp
 *
 * @link      https://www.zealousweb.com
 * @copyright Copyright (c) 2021 zealousweb
 */

namespace zealouswebcraftcms\orderonwhatsapp\variables;

use zealouswebcraftcms\orderonwhatsapp\OrderOnWhatsapp;

use Craft;

/**
 * Order on Whatsapp Variable
 *
 * Craft allows plugins to provide their own template variables, accessible from
 * the {{ craft }} global variable (e.g. {{ craft.orderOnWhatsapp }}).
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @author    zealousweb
 * @package   OrderOnWhatsapp
 * @since     1.0.0
 */
class OrderOnWhatsappVariable
{
    // Public Methods
    // =========================================================================

    /**
     * Whatever you want to output to a Twig template can go into a Variable method.
     * You can have as many variable functions as you want.  From any Twig template,
     * call it like this:
     *
     *     {{ craft.orderOnWhatsapp.exampleVariable }}
     *
     * Or, if your variable requires parameters from Twig:
     *
     *     {{ craft.orderOnWhatsapp.exampleVariable(twigValue) }}
     *
     * @param null $optional
     * @return string
     */
    public function exampleVariable($optional = null)
    {
        $result = "And away we go to the Twig template...";
        if ($optional) {
            $result = "I'm feeling optional today...";
        }
        return $result;
    }
}
