<?php

namespace Drupal\speech\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manages the call history page.
 */
class CallHistoryController extends ControllerBase {

  /**
   * The entity type manager service.
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
   * The date formatter service.
   *
   * @var DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * CallHistoryController constructor.
   *
   * @param EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param AccountInterface $current_user
   *   The current user.
   * @param DateFormatterInterface $date_formatter
   *   The date formatter service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $current_user, DateFormatterInterface $date_formatter) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('date.formatter')
    );
  }

  /**
   * Provides content for the call history page.
   */
  public function content() {
    $query = $this->entityTypeManager->getStorage('node')->getQuery('AND')
      ->condition('field_created_by', $this->currentUser->getAccountName(), '=')
      ->condition('status', 1)
      ->sort('created', 'DESC');
    $node_ids = $query->execute();

    // Builds an array consisting of the content to be displayed.
    $content = [];
    if (!empty($node_ids)) {
      $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($node_ids);
      foreach ($nodes as $node) {
        // The date the node was created at.
        $date = $this->dateFormatter->format($node->get('created')->value, 'Y-m-d H:i:s');

        $content[$node->id()]['title'] = $node->getTitle();
        $content[$node->id()]['date'] = $date;
        $content[$node->id()]['id'] = $node->id();
        }
      }

    return [
      '#theme' => 'call_history',
      '#content' => $content,
    ];
  }

}
