<?php

namespace Drupal\app_module\Annotation;

use Drupal\Component\Annotation\AnnotationInterface;
use Drupal\Component\Annotation\Plugin;

/**
 * Defines a base class for App Module plugin item annotation object.
 *
 * @see \Drupal\app_module\Plugin\AppModulePluginManager
 * @see plugin_api
 *
 * @Annotation
 */
abstract class AppModulePluginAnnotationBase extends Plugin implements AnnotationInterface {

}
