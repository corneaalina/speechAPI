<?php

namespace Drupal\speech\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\file\FileUsage\FileUsageInterface;
use Drupal\speech\Service\SpeechServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manages transcriptions.
 */
class TranscriptionForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The current user.
   *
   * @var AccountInterface
   */
  protected $currentUser;

  /**
   * The speech service.
   *
   * @var SpeechServiceInterface
   */
  protected $speechService;

  /**
   * The file usage service.
   *
   * @var FileUsageInterface
   */
  protected $fileUsage;

  /**
   * TranscriptionForm constructor.
   *
   * @param EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param AccountInterface $current_user
   *   The current user.
   * @param SpeechServiceInterface $speech_service
   *   The speech service.
   * @param FileUsageInterface $file_usage
   *   The file usage service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user, SpeechServiceInterface $speech_service, FileUsageInterface $file_usage) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->speechService = $speech_service;
    $this->fileUsage = $file_usage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('speech.speech'),
      $container->get('file.usage')
    );

  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'speech.transcription_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['recording'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('The audio recording'),
      '#description' => $this->t('The audio recording to be transcribed to text.'),
      '#required' =>  TRUE,
      '#upload_validators' => [
        'file_validate_extensions' => ['wav'],
      ],
      '#upload_location' => 'public://audio/',
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $recording = $form_state->getValue('recording');
    $transcription = [];
    $title = '';
    if (!empty($recording)) {
      // Load the object of the file by it's fid.
      $file = $this->entityTypeManager->getStorage('file')->load($recording[0]);
      $title = explode('.', $file->get('filename')->value);
      // Set the status flag permanent of the file object.
      $file->setPermanent();
      // Save the file in database.
      $file->save();
      $transcription = $this->speechService->transcribe($file);
    }

    // Creates a new node.
    $node = $this->entityTypeManager->getStorage('node')->create([
      'type' => 'speech_transcript',
      'title' => $title[0],
      'field_recording' => $recording,
      'body' => $transcription['transcript'],
      'field_confidence' => $transcription['confidence'],
      'field_created_by' => $this->currentUser->getAccountName(),
    ]);

    // Saves the node.
    $node->save();
    $this->fileUsage->add($file, 'speech', 'node', $node->id());

    $form_state->setRedirect('entity.node.canonical', array('node' => $node->id()));

  }

}
