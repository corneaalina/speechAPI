<?php

namespace Drupal\speech\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manages conference calls.
 */
class ConferenceCallForm extends FormBase {

  /**
   * The entity type manager service.
   *
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * ConferenceCallForm constructor.
   *
   * @param EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'conference_call_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['callee'] = [
      '#prefix' => '<div id="callee">',
      '#suffix' => '</div>',
      '#type' => 'textfield',
      '#title' => 'The username to call',
      '#description' => $this->t('The username to call.'),
      '#attributes' => [
        'id' => [
          'username_to_call',
        ],
      ],
    ];

    $form['call'] = [
      '#type' => 'submit',
      '#value' => 'Call',
      '#attributes' => [
        'class' => [
          'calling-button',
        ],
        'id' => [
          'call',
        ],
      ],
    ];

    $form['hangup'] = [
      '#type' => 'submit',
      '#value' => 'Hangup',
      '#attributes' => [
        'class' => [
          'hangup-button',
        ],
        'id' => [
          'hangup',
        ],
      ],
    ];

    $form['answer'] = [
      '#type' => 'submit',
      '#value' => 'Answer',
      '#attributes' => [
        'class' => [
          'answer-button'
        ],
        'id' => [
          'answer',
        ],
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
