<?php

namespace Drupal\app_module\Annotation;

/**
 * Defines an App Module settings editor plugin item annotation object.
 *
 * @see \Drupal\app_module\Plugin\AppModulePluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class AppModuleSettingsEditorPlugin extends AppModulePluginAnnotationBase {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The plugin title used in the editing UI.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title = '';

}
