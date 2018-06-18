<?php

namespace Drupal\speech\Service;

/**
 * Implements speech methods.
 */
interface SpeechServiceInterface {

  /**
   * Transcribes the audio file to text using the Speech API.
   *
   * @param $file
   *   The audio file to be transcribed.
   * @return mixed
   *   An array consisting of the text transcription and the confidence.
   */
  public function transcribe($file);

}
