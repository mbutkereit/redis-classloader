<?php
namespace marvinB8\Component\ClassLoader;

/**
 * Class RedisClassLoader.
 */
class RedisClassLoader {

  /**
   * A prefix for the redis entries.
   *
   * @var string
   */
  private $prefix;

  /**
   * A class loader object that implements the findFile() method.
   *
   * @var object
   */
  protected $decorated;

  /**
   * A redis client.
   *
   * @var \Redis
   */
  protected $redis;

  /**
   * Constructor.
   *
   * @param string $prefix The Redis namespace prefix to use.
   * @param object $decorated A class loader object that implements the findFile() method.
   *
   * @throws \RuntimeException
   * @throws \InvalidArgumentException
   */
  public function __construct($prefix, $decorated, \Redis $redis) {
    $this->redis = $redis;
    $this->prefix = $prefix;
    $this->decorated = $decorated;
  }

  /**
   * Registers this instance as an autoloader.
   *
   * @param bool $prepend Whether to prepend the autoloader or not
   */
  public function register($prepend = FALSE) {
    spl_autoload_register(array($this, 'loadClass'), TRUE, $prepend);
  }

  /**
   * Unregisters this instance as an autoloader.
   */
  public function unregister() {
    spl_autoload_unregister(array($this, 'loadClass'));
  }

  /**
   * Loads the given class or interface.
   *
   * @param string $class The name of the class
   *
   * @return bool|null True, if loaded
   */
  public function loadClass($class) {
    if ($file = $this->findFile($class)) {
      require $file;
      return TRUE;
    }
  }

  /**
   * Finds a file by class name while caching lookups to Redis.
   *
   * @param string $class A class name to resolve to file
   *
   * @return string|null
   */
  public function findFile($class) {
    $file = $this->redis->get($this->prefix . $class);
    if (!$file) {
      $this->redis->set($this->prefix . $class, $file = $this->decorated->findFile($class) ?: NULL);
    }
    return $file;
  }

  /**
   * Passes through all unknown calls onto the decorated object.
   */
  public function __call($method, $args) {
    return call_user_func_array(array($this->decorated, $method), $args);
  }
}
