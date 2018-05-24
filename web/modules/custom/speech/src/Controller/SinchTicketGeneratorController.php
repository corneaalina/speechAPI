<?php

namespace Drupal\speech\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\State\StateInterface;
use Drupal\speech\Service\SinchServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Generates Sinch ticket.
 */
class SinchTicketGeneratorController extends ControllerBase {

  /**
   * The current user.
   *
   * @var AccountInterface
   */
  protected $currentUser;

  /**
   * The sinch service.
   *
   * @var SinchServiceInterface
   */
  protected $sinchService;

  /**
   * The state service.
   *
   * @var StateInterface
   */
  protected $state;

  /**
   * SinchTicketGeneratorController constructor.
   *
   * @param AccountInterface $current_user
   *   The current user.
   * @param SinchServiceInterface $sinch_service
   *   The sinch service.
   * @param StateInterface $state
   *   The state service.
   */
  public function __construct(AccountInterface $current_user, SinchServiceInterface $sinch_service, StateInterface $state) {
    $this->currentUser = $current_user;
    $this->sinchService = $sinch_service;
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('current_user'),
      $container->get('speech.sinch'),
      $container->get('state')
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
    // The application key.
    $application_key = $this->state->get('speech.sinch_application_key');
    // The application secret key.
    $application_secret_key = $this->state->get('speech.sinch_application_secret_key');
    // The ticket availability.
    $ticket_availability = $this->state->get('speech.sinch_ticket_availability');

    // The user ticket.
    $user_ticket = [
      'identity' => [
        'type' => 'username',
        'endpoint' => $this->currentUser->getAccountName(),
      ],
      'expiresIn' => $ticket_availability,
      'applicationKey' => $application_key,
      'created' => $created_at->format('c'),
    ];

    // Json encodes the user ticket.
    $user_ticket = json_encode($user_ticket);
    $user_ticket_json = preg_replace('/\s+/', '', $user_ticket);

    // Encodes the user ticket in base 64.
    $user_ticket_base64 = $this->sinchService->base64Encode($user_ticket_json);

    // Builds the signature needed for the user ticket.
    $digest = $this->sinchService->createDigest($user_ticket_json, $application_secret_key);
    $signature = $this->sinchService->base64Encode($digest);

    // Builds and array consisting of the final user ticket.
    $response['userTicket'] = $user_ticket_base64 . ':' . $signature;
    $response['application_key'] = $application_key;

    // Encodes the response.
    $response = json_encode($response);

    return new Response($response);
  }

}
