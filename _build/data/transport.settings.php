<?php
$settings = array();

$settings['modshopify.api_key']= $modx->newObject('modSystemSetting');
$settings['modshopify.api_key']->fromArray(array(
    'key' => 'modshopify.api_key',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'modshopify',
    'area' => 'authentication',
),'',true,true);

$settings['modshopify.auth_secret']= $modx->newObject('modSystemSetting');
$settings['modshopify.auth_secret']->fromArray(array(
    'key' => 'modshopify.auth_secret',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'modshopify',
    'area' => 'authentication',
),'',true,true);

$settings['modshopify.auth_token']= $modx->newObject('modSystemSetting');
$settings['modshopify.auth_token']->fromArray(array(
    'key' => 'modshopify.auth_token',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'modshopify',
    'area' => 'authentication',
),'',true,true);

$settings['modshopify.shop_domain']= $modx->newObject('modSystemSetting');
$settings['modshopify.shop_domain']->fromArray(array(
    'key' => 'modshopify.shop_domain',
    'value' => '',
    'xtype' => 'textfield',
    'namespace' => 'modshopify',
    'area' => 'authentication',
),'',true,true);

return $settings;
