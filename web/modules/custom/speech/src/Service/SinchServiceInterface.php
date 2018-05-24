<?php

namespace Drupal\speech\Service;

/**
 * Implements Sinch methods.
 */
interface SinchServiceInterface {

  /**
   * Creates the digest for the sinch user ticket.
   *
   * @param $data
   *   The data.
   * @param $application_key
   *   The application key.
   *
   * @return string
   *   The generated hash keyed value of the data.
   */
  public function createDigest($data, $application_key);

  /**
   * Encodes the data in base64.
   *
   * @param $data
   *   The data.
   *
   * @return string
   *   The data encoded in base 64.
   */
  public function base64Encode($data);

}
