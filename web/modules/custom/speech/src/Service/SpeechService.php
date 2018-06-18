<?php

namespace Drupal\speech\Service;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Google\Cloud\Speech\SpeechClient;

/**
 * Manages the speech service.
 */
class SpeechService implements SpeechServiceInterface {

  /**
   * The state service.
   *
   * @var StateInterface
   */
  protected $state;

  /**
   * The entity type manager servce.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * SpeechService constructor.
   *
   * @param StateInterface $state
   *   The state service.
   * @param EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(StateInterface $state, EntityTypeManagerInterface $entity_type_manager) {
    $this->state = $state;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function transcribe($file) {
    // The key file.
    $key_file = $this->state->get('speech.speech_key_file');
    $key_file = $this->entityTypeManager->getStorage('file')->load($key_file[0]);

    // The speech client.
    $speech_client = new SpeechClient([
      'keyFile' => json_decode(file_get_contents($key_file->getFileUri()), true),
      'languageCode' => 'en-US'
    ]);

    // An array of options for the transcription.
    $options = [
      'encoding' => 'LINEAR16',
      'sampleRateHertz' => 44100,
    ];

    $alternatives = [];
    // Performs the speech to text transcription.
    $results = $speech_client->recognize(fopen($file->getFileUri(),'r'), $options);
    if ($results) {
      $alternatives = $results[0]->info()['alternatives'][0];
    }

    return $alternatives;
  }

}
