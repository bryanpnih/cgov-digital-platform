<?php

namespace Drupal\app_module\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for the Application module add and edit forms.
 */
class AppModuleForm extends EntityForm {

  /**
   * Constructs an AppModuleForm object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entityTypeManager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $app_module = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $app_module->label(),
      '#description' => $this->t("Label for the Application Module."),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $app_module->id(),
      '#machine_name' => [
        'exists' => [$this, 'exist'],
      ],
      '#disabled' => !$app_module->isNew(),
    ];
    $validator_doc = <<<EOD
    Application module loading strips the incoming urls of thier aliases. It then
    loads the AppModule config object for that alias and uses this path validator
    (regular expressions) to verify if the the stripped URL is a valid path for
    this app module.
EOD;

    $form['validators'] = [
      '#type' => 'details',
      '#title' => $this->t('Pattern matching.'),
      '#description' => '<p>' .
      $this->t('@validator_doc', ['@validator_doc' => $validator_doc]) .
      '</p>',
      '#open' => FALSE,
    ];
    $form['validators']['path_validator'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $app_module->getPathValidator(),
      '#description' => $this->t("The Regex to validate paths against."),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $app_module = $this->entity;
    $status = $app_module->save();

    if ($status) {
      $this->messenger()->addMessage($this->t('Saved the %label Application module.', [
        '%label' => $app_module->label(),
      ]));
    }
    else {
      $this->messenger()->addMessage($this->t('The %label Application module was not saved.', [
        '%label' => $app_module->label(),
      ]), MessengerInterface::TYPE_ERROR);
    }

    $form_state->setRedirect('entity.app_module.collection');
  }

  /**
   * Helper function to check whether an App Module configuration entity exists.
   */
  public function exist($id) {
    $entity = $this->entityTypeManager->getStorage('app_module')->getQuery()
      ->condition('id', $id)
      ->execute();
    return (bool) $entity;
  }

}
