<?php

namespace Drupal\speech\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manages user registration.
 */
class RegisterForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityTypeManager;

  /**
   * RegisterForm constructor.
   *
   * @param EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'speech.register_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#required' => true,
    ];

    $form['email'] = [
      '#type' => 'email',
      '#title' => $this->t('Email address'),
      '#required' => TRUE,
    ];

    $form['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
    ];

    $form['confirm_password'] = [
      '#type' => 'password',
      '#title' => $this->t('Confirm password'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $user = $this->entityTypeManager->getStorage('user');

    // Validates the username.
    if (empty($values['username'])) {
      $form_state->setErrorByName('username', $this->t('Username missing!'));
    }
    elseif (!empty($user->loadByProperties(['name' => $values['username']]))) {
      $form_state->setErrorByName('username', $this->t('@username is already registered!', ['@username' => $values['username']]));
    }

    // Validates the email.
    if (empty($values['email'])) {
      $form_state->setErrorByName('email', $this->t('Email address missing!'));
    }
    elseif (!empty($user->loadByProperties(['mail' => $values['email']]))) {
      $form_state->setErrorByName('email', $this->t('@email is already registered!', ['@email' => $values['email']]));
    }

    // Validates the password.
    if (empty($values['password'])) {
      $form_state->setErrorByName('password', t('Password is missing!'));
    }
    elseif (strlen($values['password']) < 5) {
      $form_state->setErrorByName('password', t('Password has to be at least 5 characters long.!'));
    }
    elseif (empty($values['confirm_password'])) {
      $form_state->setErrorByName('confirm_password', t('Please confirm password!'));
    }
    elseif ($values['password'] !== $values['confirm_password']) {
      $form_state->setErrorByName('confirm_password', t('The specified passwords do not match!'));
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    // Creates and saves the user.
    $user = $this->entityTypeManager->getStorage('user')->create();
    $user->setPassword($values['password']);
    $user->setEmail($values['email']);
    $user->set('name', $values['username']);
    $user->activate();
    $user->save();

    $form_state->setRedirect('user.login');
  }

}