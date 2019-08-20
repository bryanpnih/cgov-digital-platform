<?php

namespace Drupal\app_module\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\app_module\AppModuleInterface;

/**
 * Defines the Application Module entity.
 *
 * @ConfigEntityType(
 *   id = "app_module",
 *   label = @Translation("Application Module"),
 *   handlers = {
 *     "list_builder" = "Drupal\app_module\Controller\AppModuleListBuilder",
 *     "form" = {
 *       "add" = "Drupal\app_module\Form\AppModuleForm",
 *       "edit" = "Drupal\app_module\Form\AppModuleForm",
 *       "delete" = "Drupal\app_module\Form\AppModuleDeleteForm",
 *     }
 *   },
 *   config_prefix = "app",
 *   admin_permission = "administer app modules",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "path_validator",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/system/app_module/{app_module}",
 *     "delete-form" = "/admin/config/system/app_module/{app_module}/delete",
 *   }
 * )
 */
class AppModule extends ConfigEntityBase implements AppModuleInterface {

  /**
   * The App Module ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The App Module label.
   *
   * @var string
   */
  protected $label;

  /**
   * The App Module path validator.
   *
   * Used to ensure a requested URL matches an app module path.
   *
   * @var string
   */
  protected $path_validator;

  /**
   * {@inheritdoc}
   */
  public function getPathValidator() {
    return $this->path_validator;
  }

  /**
   * {@inheritdoc}
   */
  public function setPathValidator($path_validator) {
    $this->path_validator = $path_validator;
    return $this;
  }

}
