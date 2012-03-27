<?php

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tstart = $mtime;
set_time_limit(0);
 
/* define package names */
define('PKG_NAME','modShopify');
define('PKG_NAME_LOWER','modshopify');
define('PKG_VERSION','1.0');
define('PKG_RELEASE','alpha');

/* define build paths */
$root = dirname(dirname(__FILE__)).'/';
$sources = array(
  'root' => $root,
  'build' => $root . '_build/',
  'data' => $root . '_build/data/',
  'resolvers' => $root . '_build/resolvers/',
  'chunks' => $root.'core/components/'.PKG_NAME_LOWER.'/elements/chunks/',
  'snippets' => $root.'core/components/'.PKG_NAME_LOWER.'/elements/snippets/',
  'lexicon' => $root . 'core/components/'.PKG_NAME_LOWER.'/lexicon/',
  'docs' => $root.'core/components/'.PKG_NAME_LOWER.'/docs/',  
  'source_assets' => $root.'assets/components/'.PKG_NAME_LOWER,
  'source_core' => $root.'core/components/'.PKG_NAME_LOWER,
);
unset($root);

/* override with your own defines here (see build.config.sample.php) */
require_once $sources['build'] . 'build.config.php';
require_once MODX_CORE_PATH . 'model/modx/modx.class.php';

$modx= new modX();
$modx->initialize('mgr');
echo '<pre>'; /* used for nice formatting of log messages */
$modx->setLogLevel(modX::LOG_LEVEL_INFO);
$modx->setLogTarget('ECHO');
echo 'Packing '.PKG_NAME_LOWER.'-'.PKG_VERSION.'-'.PKG_RELEASE.'<pre>'; flush();
 
$modx->loadClass('transport.modPackageBuilder','',false, true);
$builder = new modPackageBuilder($modx);
$builder->createPackage(PKG_NAME_LOWER,PKG_VERSION,PKG_RELEASE);
$builder->registerNamespace(PKG_NAME_LOWER,false,true,'{core_path}components/'.PKG_NAME_LOWER.'/');
$modx->getService('lexicon','modLexicon');

/* load system settings */
$settings = include $sources['data'].'transport.settings.php';
$attr= array(
  xPDOTransport::UNIQUE_KEY => 'key',
  xPDOTransport::PRESERVE_KEYS => true,
  xPDOTransport::UPDATE_OBJECT => false,
);
foreach ($settings as $setting) {
  $vehicle = $builder->createVehicle($setting,$attr);
  $builder->putVehicle($vehicle);
}
$modx->log(modX::LOG_LEVEL_INFO,'Packaged in '.count($settings).' settings.'); flush();
unset($settings,$setting,$attr);

$category= $modx->newObject('modCategory');
$category->set('id',1);
$category->set('category',PKG_NAME);
$modx->log(modX::LOG_LEVEL_INFO,'Packaged in category.'); flush();

/* add chunks */
$chunks = include $sources['data'].'transport.chunks.php';
if (is_array($chunks)) {
  $category->addMany($chunks, 'Chunks');
} else { $modx->log(modX::LOG_LEVEL_FATAL,'Adding snippets failed.'); }
$modx->log(modX::LOG_LEVEL_INFO,'Packaged in '.count($chunks).' chunks.'); flush();
unset($chunks);

/* add snippets */
$snippets = include $sources['data'].'transport.snippets.php';
if (is_array($snippets)) {
    $category->addMany($snippets,'Snippets');
} else { $modx->log(modX::LOG_LEVEL_FATAL,'Adding snippets failed.'); }
$modx->log(modX::LOG_LEVEL_INFO,'Packaged in '.count($snippets).' snippets.'); flush();
unset($snippets);

/* create category vehicle */
$attr = array(
  xPDOTransport::UNIQUE_KEY => 'category',
  xPDOTransport::PRESERVE_KEYS => false,
  xPDOTransport::UPDATE_OBJECT => true,
  xPDOTransport::RELATED_OBJECTS => true,
  xPDOTransport::RELATED_OBJECT_ATTRIBUTES => array (
    'Chunks' => array(
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::UNIQUE_KEY => 'name',
    ),
    'Snippets' => array(
        xPDOTransport::PRESERVE_KEYS => false,
        xPDOTransport::UPDATE_OBJECT => true,
        xPDOTransport::UNIQUE_KEY => 'name',
    ),
  ),
);
$vehicle = $builder->createVehicle($category,$attr);

/* add file resolvers */
$vehicle->resolve('file',array(
    'source' => $sources['source_core'],
    'target' => "return MODX_CORE_PATH . 'components/';",
));

/* add setupoptions resolver */
$vehicle->resolve('php',array(
    'source' => $sources['resolvers'] . 'setupoptions.resolver.php',
));
$modx->log(modX::LOG_LEVEL_INFO,'Packaged in resolvers.'); flush();

$builder->putVehicle($vehicle);

/* now pack in the license file, readme and setup options */
$builder->setPackageAttributes(array(
    'license' => file_get_contents($sources['docs'] . 'license.txt'),
    'readme' => file_get_contents($sources['docs'] . 'readme.txt'),
    'changelog' => file_get_contents($sources['docs'] . 'changelog.txt'),
    'setup-options' => array(
      'source' => $sources['build'].'setup.options.php'
    ),
));

$modx->log(modX::LOG_LEVEL_INFO,'Packaged in package attributes.'); flush();

/* zip up package */
$modx->log(modX::LOG_LEVEL_INFO,'Packing up transport package zip...');
$builder->pack();
 
$modx->log(modX::LOG_LEVEL_INFO,'Packing...'); flush();
$builder->pack();

$mtime = microtime();
$mtime = explode(" ", $mtime);
$mtime = $mtime[1] + $mtime[0];
$tend = $mtime;
$totalTime = ($tend - $tstart);
$totalTime = sprintf("%2.4f s", $totalTime);

$modx->log(modX::LOG_LEVEL_INFO,"\n<br />Package Built.<br />\nExecution time: {$totalTime}\n");
exit ();
