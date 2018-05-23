<?php

namespace Drupal\speech\Service;

/**
 * Manages the Sinch service.
 */
class SinchService implements SinchServiceInterface {

  /**
   * {@inheritdoc}
   */
  public function createDigest($data, $application_secret) {
    return trim(hash_hmac('sha256', $data, base64_decode($application_secret), true));
  }

  /**
   * {@inheritdoc}
   */
  public function base64Encode($data) {
    return trim(base64_encode($data));
  }

}
