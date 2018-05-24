<?php

namespace Drupal\speech\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manages conference call page.
 */
class ConferenceCallController extends ControllerBase {

  /**
   * The form builder service.
   *
   * @var FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * ConferenceCallController constructor.
   *
   * @param FormBuilderInterface $form_builder
   *   The form builder service.
   */
  public function __construct(FormBuilderInterface $form_builder) {
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static (
      $container->get('form_builder')
    );
  }

  /**
   * Provides content for the conference call page.
   */
  public function content() {
    // The conference call form.
    $form = $this->formBuilder->getForm('\Drupal\speech\Form\ConferenceCallForm');
    // The module path.
    $module_path = drupal_get_path('module', 'speech');

    return [
      '#theme' => 'conference_call',
      '#form' => $form,
      '#path' => $module_path,
    ];

  }

}
