<?php
/**
 * Resolves setup-options settings by setting Shopify API options.
 *
 */

$success = false;
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
    case xPDOTransport::ACTION_INSTALL:
    case xPDOTransport::ACTION_UPGRADE:
        /* shopDomain */
        $setting = $object->xpdo->getObject('modSystemSetting',array('key' => 'modshopify.shop_domain'));
        if ($setting != null) {
            $setting->set('value',$options['shopDomain']);
            $setting->save();
        } else {
            $object->xpdo->log(xPDO::LOG_LEVEL_ERROR,'[modShopify] shopDomain setting could not be found, so the setting could not be changed.');
        }
 
        /* apiKey */
        $setting = $object->xpdo->getObject('modSystemSetting',array('key' => 'modshopify.api_key'));
        if ($setting != null) {
            $setting->set('value',$options['apiKey']);
            $setting->save();
        } else {
            $object->xpdo->log(xPDO::LOG_LEVEL_ERROR,'[modShopify] apiKey setting could not be found, so the setting could not be changed.');
        }
        
        /* authSecret */
        $setting = $object->xpdo->getObject('modSystemSetting',array('key' => 'modshopify.auth_secret'));
        if ($setting != null) {
            $setting->set('value',$options['authSecret']);
            $setting->save();
        } else {
            $object->xpdo->log(xPDO::LOG_LEVEL_ERROR,'[modShopify] authSecret setting could not be found, so the setting could not be changed.');
        }
        
        /* authToken */
        $setting = $object->xpdo->getObject('modSystemSetting',array('key' => 'modshopify.auth_token'));
        if ($setting != null) {
            $setting->set('value',$options['authToken']);
            $setting->save();
        } else {
            $object->xpdo->log(xPDO::LOG_LEVEL_ERROR,'[modShopify] authToken setting could not be found, so the setting could not be changed.');
        }
 
        $success= true;
        break;
    case xPDOTransport::ACTION_UNINSTALL:
        $success= true;
        break;
}
return $success;
