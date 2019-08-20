<?php

namespace Drupal\app_module;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface defining an App Module entity.
 */
interface AppModuleInterface extends ConfigEntityInterface {

  /**
   * Gets the App Module path validator.
   *
   * @return string
   *   The regex pattern to match the paths.
   */
  public function getPathValidator();

  /**
   * Sets the App Module path validator.
   *
   * @param string $pathValidator
   *   The regex pattern to validate app module paths against.
   *
   * @return $this
   */
  public function setPathValidator($pathValidator);

}
