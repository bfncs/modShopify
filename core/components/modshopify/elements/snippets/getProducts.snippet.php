<?php

$defaults = array(  
  'limit' => 50,
  'page' => 1,
  'published_status' => 'published',
  
  'containerTpl' => 'modshopifyOuterTpl',
  'productTpl' => 'modshopifyProductTpl',
  'productImgTpl' => 'modshopifyProductImgTpl',
  'productVariantTpl' => 'modshopifyProductVariantTpl',
  
  'thumbsWidth' => 200,
  'thumbsHeight' => 200,
  'thumbsArgs' => '&zc=1'
);

$scriptProperties = array_merge($defaults,$scriptProperties);
$scriptProperties['thumbs_options'] = "";
if (!empty($scriptProperties['thumbsWidth'])) $scriptProperties['thumbs_options'] .= "&w=" . $scriptProperties['thumbsWidth'];
if (!empty($scriptProperties['thumbsHeight'])) $scriptProperties['thumbs_options'] .= "&h=" . $scriptProperties['thumbsHeight'];
$scriptProperties['thumbs_options'] .= $scriptProperties['thumbsArgs'];
unset($scriptProperties['thumbsWidth'], $scriptProperties['thumbsHeight'], $scriptProperties['thumbsArgs']);

$path = $modx->getOption('modshopify.core_path', $config, $modx->getOption('core_path').'components/modshopify/');
$ms = $modx->getService('modshopify', 'ModShopify', $path . 'model/', $scriptProperties);

$output = array();
$shop = $ms->getShop();
if(empty($shop)) return;

$products = $ms->getProducts();
if(empty($products)) return;

foreach ($products as $product) {
  $variants = array();
  foreach ($product['variants'] as $variant) {
    $variant['price'] = preg_replace('/{{.*}}/', $variant['price'], $shop['money_format']);
    $variants[] = $ms->getChunk($scriptProperties['productVariantTpl'], $variant);
  }
  $product['variants'] = implode($scriptProperties['variantSeparator'], $variants);
  if (!empty($product['images'])) {
    $images = array();
    foreach ($product['images'] as $image) {
      $image['alt'] = trim($product['title'], "'\"");
      if (!empty($scriptProperties['thumbs_options'])) {
        $image['src'] = $modx->runSnippet("phpthumbof", array(
          'input' => $image['src'],
          'options' => $scriptProperties['thumbs_options']
        ));
      }
      $images[] = $ms->getChunk($scriptProperties['productImgTpl'], $image);
    }
    $product['images'] = implode($scriptProperties['imageSeparator'], $images);
  } else {
    unset ($product['images']);
  }
  $product = array_merge(
    $product,
    array(
      'domain' => $shop['domain']
    )
  );
  $output[] = $ms->getChunk($scriptProperties['productTpl'], $product);
}
$output = implode($scriptProperties['productSeparator'], $output);

$output = $ms->getChunk($scriptProperties['containerTpl'], array(products => $output));

return $output;
