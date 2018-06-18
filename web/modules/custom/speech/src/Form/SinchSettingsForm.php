<?php

namespace Drupal\speech\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manages the Sinch configurations.
 */
class SinchSettingsForm extends FormBase {

  /**
   * The state service.
   *
   * @var StateInterface
   */
  protected $state;

  /**
   * SinchSettingsForm constructor.
   *
   * @param StateInterface $state
   *   The state service.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
       $container->get('state')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'speech.sinch_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // The application key.
    $application_key = $this->state->get('speech.sinch_application_key');
    // The application secret key.
    $application_secret_key = $this->state->get('speech.sinch_application_secret_key');
    // The ticket availability.
    $ticket_availability = $this->state->get('speech.sinch_ticket_availability');

    $form['application_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The application key'),
      '#description' => $this->t('The sinch application key.'),
      '#default_value' => $application_key ? $application_key : null,
      '#required' => true,
    ];

    $form['application_secret_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The application secret key'),
      '#description' => $this->t('The sinch application secret key.'),
      '#default_value' => $application_secret_key ? $application_secret_key : null,
      '#required' => true,
    ];

    $form['ticket_availability'] = [
      '#type' => 'textfield',
      '#title' => $this->t('The ticket availability'),
      '#description' => $this->t('The amount of time the sinch user ticket is available.'),
      '#default_value' => $ticket_availability ? $ticket_availability : null,
      '#required' => true,
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configurations'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Sets the values in state.
    $this->state->set('speech.sinch_application_key', $form_state->getValue('application_key'));
    $this->state->set('speech.sinch_application_secret_key', $form_state->getValue('application_secret_key'));
    $this->state->set('speech.sinch_ticket_availability', $form_state->getValue('ticket_availability'));
  }

}
