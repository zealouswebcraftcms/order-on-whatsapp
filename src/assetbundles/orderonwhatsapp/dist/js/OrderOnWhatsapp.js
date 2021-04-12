/**
 * Order on Whatsapp plugin for Craft CMS
 *
 * Order on Whatsapp JS
 *
 * @author    zealousweb
 * @copyright Copyright (c) 2021 zealousweb
 * @link      https://www.zealousweb.com
 * @package   OrderOnWhatsapp
 * @since     1.0.0
 */
 $( document ).ready(function() {

    if($('.allproducts').prop('checked') == false) {
        $(".producttypes-box select").removeAttr("disabled");
    } else {
        $(".producttypes-box select").attr("disabled", true);
    }
    
    $(".allproducts").on("change", function() {
        if($('.allproducts').prop('checked') == true) {
            $(".producttypes-box select").attr("disabled", true);
            $(".producttypes-box select option").removeAttr('selected');

        } else {
            $(".producttypes-box select").removeAttr("disabled");
        }
    });

    if($('.shareallproducts').prop('checked') == false) {
        $(".shareproducttypes-box select").removeAttr("disabled");
        $(".shareallproducts").val(0);
    } else {
        $(".shareproducttypes-box select").attr("disabled", true);
    }
    
    $(".shareallproducts").on("change", function() {
        if($('.shareallproducts').prop('checked') == true) {
            $(".shareallproducts").val(1);
            $(".shareproducttypes-box select").attr("disabled", true);
            $(".shareproducttypes-box select option").removeAttr('selected');
        } else {
            $(".shareallproducts").val(0);
            $(".shareproducttypes-box select").removeAttr("disabled");
        }
    });
    $("#settings-enableshare").on("change", function(e) {
        if ($("#settings-enableshare").hasClass("on")){
            $("#settings-share-button-text").attr("disabled", false);
            $(".sharedata-box").attr("disabled", false);
            $(".sharedata-box select").attr("disabled", false);
            $("#settings-sharebackground-container input").attr('disabled',false);
            $("#settings-sharetext-container input").attr('disabled',false);
            

        } else {
            $("#settings-share-button-text").attr("disabled", false);
            $(".sharedata-box").attr("disabled", true);
            $(".sharedata-box select").attr("disabled", true);
            $("#settings-sharebackground-container input").attr('disabled',true);
            $("#settings-sharetext-container input").attr('disabled',true);

        }
        
       
    });

    if(($("#settings-enableshare").hasClass("on"))){
        $("#settings-share-button-text").attr("disabled", false);
        $(".sharedata-box").attr("disabled", false);
        $(".sharedata-box select").attr("disabled", false);
        $("#settings-sharebackground-container input").attr('disabled',false);
        $("#settings-sharetext-container input").attr('disabled',false);    
      }
      else{
      $("#settings-share-button-text").attr("disabled", true);
      $(".sharedata-box").attr("disabled", true);
      $(".sharedata-box select").attr("disabled", true);
      $("#settings-sharebackground-container input").attr('disabled',true);
      $("#settings-sharetext-container input").attr('disabled',true);

      }
      if($(".shareallproducts").val() =="1") { 
        $(".shareproducttypes-box select").attr("disabled", true);
        }
       
});
