<?php
/**
 * Build the setup options form.
 *
 * @package quip
 * @subpackage build
 */
/* set some default values */
$values = array(
  'shopDomain' => 'my-sample-shop.myshopify.com',
  'apiKey' => '',
  'authSecret' => '',
  'authToken' => '',
);
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
  case xPDOTransport::ACTION_INSTALL:
  case xPDOTransport::ACTION_UPGRADE:
      $setting = $modx->getObject('modSystemSetting',array('key' => 'modshopify.shop_domain'));
      if ($setting != null) { $values['shopDomain'] = $setting->get('value'); }
      unset($setting);

      $setting = $modx->getObject('modSystemSetting',array('key' => 'modshopify.api_key'));
      if ($setting != null) { $values['apiKey'] = $setting->get('value'); }
      unset($setting);

      $setting = $modx->getObject('modSystemSetting',array('key' => 'modshopify.auth_secret'));
      if ($setting != null) { $values['authSecret'] = $setting->get('value'); }
      unset($setting);
      
      $setting = $modx->getObject('modSystemSetting',array('key' => 'modshopify.auth_token'));
      if ($setting != null) { $values['authToken'] = $setting->get('value'); }
      unset($setting);
  break;
  case xPDOTransport::ACTION_UNINSTALL: break;
}
 
$output = '<label for="modshopify-shopDomain">Shopify Shop Domain:</label>
<input type="text" name="shopDomain" id="modshopify-shopDomain" width="300" value="'.$values['shopDomain'].'" />
<br /><br />
 
<label for="modshopify-apiKey">Shopify App API Key:</label>
<input type="text" name="apiKey" id="modshopify-apiKey" width="300" value="'.$values['apiKey'].'" />
<br /><br />
 
<label for="modshopify-authSecret">Shopify Authentication Secret ("Password"):</label>
<input type="text" name="authSecret" id="modshopify-authSecret" width="300" value="'.$values['authSecret'].'" />
<br /><br />

<label for="modshopify-authToken">Shopify Authentication Token ("Shared Secret"):</label>
<input type="text" name="authToken" id="modshopify-authToken" width="300" value="'.$values['authToken'].'" />';
 
return $output;
