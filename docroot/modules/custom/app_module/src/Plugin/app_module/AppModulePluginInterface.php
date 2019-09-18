<?php

namespace Drupal\app_module\Plugin\app_module;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an interface for all Application Module plugins.
 */
interface AppModulePluginInterface extends PluginInspectionInterface {

  /**
   * Return the human readable name of the plugin.
   *
   * This appears on the ui in plugin selection interfaces.
   *
   * @return string
   *   The human readable name.
   */
  public function pluginTitle();

  /**
   * Return the id of the plugin.
   *
   * @return string
   *   The id of the plugin.
   */
  public function pluginId();

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition);

  /**
   * Builds and returns a renderable array for a route of this appmodule plugin.
   *
   * @param string $path
   *   The requested app module path as a string. This is relative to the
   *   app module alias. For the node /foo/bar, if the url requested is
   *   /foo/bar/bazz/blah then the $path would be /bazz/blah.
   * @param array $options
   *   The settings for this instance of the AppModule on an entity.
   *
   * @return array
   *   A renderable array representing the content of the block.
   *
   * @see \Drupal\app_module\AppModuleRenderArrayBuilder
   */
  public function buildForRoute($path, array $options = []);

  /**
   * Gets the machine id of the app module route.
   *
   * This should match the same format as a machine id.
   *
   * @param string $path
   *   The requested app module path as a string. This is relative to the
   *   app module alias. For the node /foo/bar, if the url requested is
   *   /foo/bar/bazz/blah then the $path would be /bazz/blah.
   * @param array $options
   *   The settings for this instance of the AppModule on an entity.
   *
   * @return string
   *   A machine id compatible route id.
   */
  public function getAppRouteId($path, array $options = []);

  /**
   * Gets cacheability metadata for an app modules route.
   *
   * This is used by the AppModuleRenderArrayBuilder to generate the build
   * object. Best practice is to add cache tags to a render array and provide
   * a #preRender callback so the item can be cached without requiring the
   * markup to be built each time, only to get it from the render cache.
   *
   * @param string $path
   *   The requested app module path as a string. This is relative to the
   *   app module alias. For the node /foo/bar, if the url requested is
   *   /foo/bar/bazz/blah then the $path would be /bazz/blah.
   * @param array $options
   *   The settings for this instance of the AppModule on an entity.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   Cacheability metadata for the specific route. Most importantly this
   *   should contain the cacheTags for parameters in the request. E.g.
   *   the dictionary term id, the parameters of a search, etc. Additionally
   *   this should also take into account any parameters in $options. For
   *   example if the script generated on the page is tied to the params
   *   in the options you will need a way to clear them out if you want to
   *   target a specific item. As this is a CacheableMetadata object you
   *   can also include any contexts or cache age settings as well.
   *   See https://www.drupal.org/docs/8/api/render-api/cacheability-of-render-arrays#s-the-thought-process
   */
  public function getCacheInfoForRoute($path, array $options = []);

}
