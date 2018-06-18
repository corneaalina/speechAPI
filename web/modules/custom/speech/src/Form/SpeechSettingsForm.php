<?php

namespace Drupal\speech\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manages Speech Configurations.
 */
class SpeechSettingsForm extends FormBase {

  /**
   * The state service.
   *
   * @var StateInterface
   */
  protected $state;

  /**
   * The entity type manager service.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The file usage service.
   *
   * @var FileUsageInterface
   */
  protected $fileUsage;

  /**
   * SpeechSettingsForm constructor.
   *
   * @param StateInterface $state
   *   The state service.
   * @param EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param FileUsageInterface $file_usage
   *   The file usage service.
   */
  public function __construct(StateInterface $state, EntityTypeManagerInterface $entity_type_manager, FileUsageInterface $file_usage) {
    $this->state = $state;
    $this->entityTypeManager = $entity_type_manager;
    $this->fileUsage = $file_usage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('state'),
      $container->get('entity_type.manager'),
      $container->get('file.usage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'speech.speech_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // The key file.
    $key_file = $this->state->get('speech.speech_key_file');

    $form['key_file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('The key file'),
      '#description' => $this->t('The speech json key file.'),
      '#required' => TRUE,
      '#upload_validators' => [
        'file_validate_extensions' => ['json'],
      ],
      '#upload_location' => 'public://files/',
      '#default_value' => $key_file ? $key_file : null,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configurations'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // The fid of the file.
    $fid = $form_state->getValue('key_file')[0];
    // Load the object of the file by it's fid.
    $file = $this->entityTypeManager->getStorage('file')->load($fid);
    // Add this file in database.
    $this->fileUsage->add($file, 'speech', 'user', 1);
    // Set the status flag permanent of the file object.
    $file->setPermanent();
    // Save the file in database.
    $file->save();

    // Sets the value in state.
    $this->state->set('speech.speech_key_file', $form_state->getValue('key_file'));
  }

}
