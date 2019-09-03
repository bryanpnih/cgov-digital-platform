<?php

namespace Drupal\app_module\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Plugin type manager for all app module plugins.
 */
class AppModulePluginManager extends DefaultPluginManager {

  /**
   * Constructs a AppModulePluginManager object.
   *
   * @param string $type
   *   The plugin type, for example settings editor.
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct($type, \Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    $plugin_definition_annotation_name = 'Drupal\app_module\Annotation\AppModule' . Container::camelize($type);
    parent::__construct("Plugin/app_module/$type", $namespaces, $module_handler, 'Drupal\app_module\Plugin\AppModule\AppModulePluginInterface', $plugin_definition_annotation_name);
    $this->defaults += [
      'parent' => 'parent',
      'plugin_type' => $type,
      'register_theme' => TRUE,
    ];
    $this->alterInfo('app_module_plugins_' . $type);
    $this->setCacheBackend($cache_backend, "app_module:$type");
  }

}
