<?php
/**
 * Order on Whatsapp plugin for Craft CMS 3.x
 *
 * Order on Whatsapp
 *
 * @link      https://www.zealousweb.com
 * @copyright Copyright (c) 2021 zealousweb
 */

namespace zealouswebcraftcms\orderonwhatsapp\models;

use zealouswebcraftcms\orderonwhatsapp\OrderOnWhatsapp;

use Craft;
use craft\base\Model;

/**
 * OrderOnWhatsapp Settings Model
 *
 * This is a model used to define the plugin's settings.
 *
 * Models are containers for data. Just about every time information is passed
 * between services, controllers, and templates in Craft, it’s passed via a model.
 *
 * https://craftcms.com/docs/plugins/models
 *
 * @author    zealousweb
 * @package   OrderOnWhatsapp
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * Some field model attribute
     *
     * @var string
     */
    public $countrycode;
    public $whatsappnumber;
    public $buttontext;
    public $backgroundcolor;
    public $textcolor;
    public $shoppage;
    public $detailpage;
    public $cartpage;
    public $allproducts;
    public $producttypes;
    public $sharebuttontext;
    public $sharebackgroundcolor;
    public $sharetextcolor;
    public $shareallproducts;
    public $shareproducttypes;
    public $enableshare;
    public $checkoutpage;

    // Public Methods
    // =========================================================================

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * More info: http://www.yiiframework.com/doc-2.0/guide-input-validation.html
     *
     * @return array
     */
    public function rules()
    {
        return [
           
            [['whatsappnumber'], 'required','message' => ' Whatsapp number can not be blank'],
            [['whatsappnumber'], 'number','message' => 'Whatsapp number must be number only.'],
            [['whatsappnumber'], 'customvalidation'],
            [['countrycode'], 'required','message' => ' Country code can not be blank']

        ];
    }

    public function customvalidation($attribute)
    {
      if(strlen(trim($this->$attribute)) > 12 )
        $this->addError($attribute, 'Whatsapp number can not be greater than 12 digit');

      if(strlen(trim($this->$attribute)) < 4 )
        $this->addError($attribute, 'Whatsapp number can not be less than 4 digit');

      if(trim($this->$attribute) == '0')
        $this->addError($attribute, 'First digit of whatsapp number can not be 0');    
    }
}
