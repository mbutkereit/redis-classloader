
##Drupal 8 (/Users/marvin/WebDev/drupal/web/sites/default/settings.php)

$class_loader->addPsr4('Drupal\\redis\\', DRUPAL_ROOT . '/modules/contrib/redis/src');

if ($settings['hash_salt']) {
  $prefix = 'drupal.' . hash('sha256', 'drupal.' . $settings['hash_salt']);
  $redis = new \Redis();
  $redis->connect($settings['redis.connection']['host'],$settings['redis.connection']['port']);
  $redis_loader = new marvinB8\Component\ClassLoader\RedisClassLoader($prefix, $class_loader,$redis);
  unset($prefix);
  $class_loader->unregister();
  $redis_loader->register();
  $class_loader = $apc_redis;
}
