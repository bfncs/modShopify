<?php
$chnks = array(
  'modshopifyOuter' => 'Tpl chunk for the outer container',
  'modshopifyProduct' => 'Tpl chunk for a single product',
  'modshopifyProductImg' => 'Tpl chunk for a single product image',
  'modshopifyProductVariant' => 'Tpl chunk for a single product variant',
);

function getChunkContent($filename = '') {
    $o = file_get_contents($filename);
    $o = trim($o);
    return $o;
}

$chunks = array();
$idx = 0;

foreach ($chnks as $cn => $cdesc) {
  $idx++;
  $chunks[$idx] = $modx->newObject('modChunk');
  $chunks[$idx]->fromArray(array(
   'id' => $idx,
   'name' => $cn,
   'description' => $cdesc,
   'snippet' => getChunkContent($sources['chunks'].$cn.'.tpl')
  ));
}

return $chunks;

?>
