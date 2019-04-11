<?php

namespace Drupal\cgov_list\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsFormatterBase;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "cgov_list_item_entity_ref_rev_view",
 *   label = @Translation("CGov List Item Rendered entity"),
 *   description = @Translation("Displays the referenced entities using the selected List Style view."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class ListItemFormatter extends EntityReferenceRevisionsFormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a StringFormatter instance.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings settings.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   *   The logger factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   */
  public function __construct(
    $plugin_id,
    $plugin_definition,
    FieldDefinitionInterface $field_definition,
    array $settings,
    $label,
    $view_mode,
    array $third_party_settings,
    LoggerChannelFactoryInterface $logger_factory,
    EntityTypeManagerInterface $entity_type_manager,
    EntityDisplayRepositoryInterface $entity_display_repository
  ) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->loggerFactory = $logger_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityDisplayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('logger.factory'),
      $container->get('entity_type.manager'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    // NOTE: We reference the parent's parent, otherwise we would get the view
    // mode setting we are trying to hide.
    return [
      'link' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // We have no options.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    // We have no options, so no summary.
    return [];
  }

  /**
   * {@inheritdoc}
   *
   * This is pretty much a copy paste from the ERR Formatter. We need to get
   * the view mode from the list entity and no the settings, so we need to
   * redo all the logic. This will cause issues someday I am sure. :(
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {

    $list_entity = $items->getEntity();

    $view_mode = $this->getViewMode($list_entity);
    $elements = [];

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      // Protect ourselves from recursive rendering.
      static $depth = 0;
      $depth++;
      if ($depth > 20) {
        $this->loggerFactory->get('entity')->error('Recursive rendering detected when rendering entity @entity_type @entity_id. Aborting rendering.', ['@entity_type' => $entity->getEntityTypeId(), '@entity_id' => $entity->id()]);
        return $elements;
      }

      $view_builder = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
      $elements[$delta] = $view_builder->view($entity, $view_mode, $entity->language()->getId());

      // Add a resource attribute to set the mapping property's value to the
      // entity's url. Since we don't know what the markup of the entity will
      // be, we shouldn't rely on it for structured data such as RDFa.
      if (!empty($items[$delta]->_attributes) && !$entity->isNew() && $entity->hasLinkTemplate('canonical')) {
        $items[$delta]->_attributes += [
          'resource' => $entity->toUrl()->toString(),
        ];
      }
      $depth = 0;
    }

    return $elements;
  }

  /**
   * Gets the view mode to used based on list style field.
   *
   * @param \Drupal\core\Entity\EntityInterface $entity
   *   A cgov_list entity.
   */
  private function getViewMode(EntityInterface $entity) {
    $list_item_mode = $entity->field_list_item_style->value;
    // Fallback to title only.
    if ($list_item_mode == NULL) {
      $list_item_mode = 'list_item_title';
    }
    return $list_item_mode;
  }

  /**
   * {@inheritdoc}
   */
  public static function isApplicable(FieldDefinitionInterface $field_definition) {
    // This formatter is only available for entity types that have a view
    // builder.
    $target_type = $field_definition->getFieldStorageDefinition()->getSetting('target_type');
    return \Drupal::entityTypeManager()->getDefinition($target_type)->hasViewBuilderClass();
  }

}
