<?php

namespace Drupal\speech\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Manages conference calls.
 */
class ConferenceCallForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'speech.conference_call_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['callee'] = [
      '#type' => 'textfield',
      '#placeholder' => $this->t('Username to call'),
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

    $form['#attached']['library'][] = 'speech/conference_call';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }

}
