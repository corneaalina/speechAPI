<?php

namespace Drupal\speech\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Site\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generates Sinch ticket.
 */
class SinchTicketGeneratorController extends ControllerBase {

  /**
   * The application key.
   *
   * @var string
   */
  private $applicationKey;

  /**
   * The application secret key.
   *
   * @var string
   */
  private $applicationSecret;

  /**
   * The current user.
   *
   * @var AccountInterface
   */
  protected $currentUser;

  /**
   * SinchTicketGeneratorController constructor.
   *
   * @param AccountInterface $current_user
   *   The current user.
   */
  public function __construct(AccountInterface $current_user) {
    $this->applicationKey = Settings::get('sinch-key');
    $this->applicationSecret = Settings::get('sinch-secret');
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('current_user')
    );
  }

  /**
   * Manages the content on the ticket generator page.
   *
   * @return Response
   */
  public function content() {
    // The current time.
    $created_at = new \DateTime('now');

    // The user ticket.
    $user_ticket = [
      'identity' => [
        'type' => 'username',
        'endpoint' => $this->currentUser->getAccountName(),
      ],
      'expiresIn' => 3600,
      'applicationKey' => $this->applicationKey,
      'created' => $created_at->format('c'),
    ];

    // Json encodes the user ticket.
    $user_ticket_json = preg_replace('/\s+/', '', json_encode($user_ticket));

    // Encodes the user ticket in base 64.
    $user_ticket_base64 = $this->base64Encode($user_ticket_json);

    // Builds the signature needed for the user ticket.
    $digest = $this->createDigest($user_ticket_json);
    $signature = $this->base64Encode($digest);

    // Builds and array consisting of the final user ticket.
    $response['userTicket'] = $user_ticket_base64 . ':' . $signature;

    // Encodes the response.
    $response = json_encode($response);

    return new Response($response);
  }

  /**
   * Creates the digest.
   *
   * @param $data
   *   The data.
   *
   * @return string
   *   The generated hash keyed value of the data.
   */
  public function createDigest($data) {
    return trim(hash_hmac('sha256', $data, base64_decode($this->applicationSecret), true));
  }

  /**
   * Encodes the data in base64.
   *
   * @param $data
   *   The data.
   *
   * @return string
   *   The data encoded in base 64.
   */
  public function base64Encode($data) {
    return trim(base64_encode($data));
  }

}
