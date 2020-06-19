<?php

namespace Drupal\gatsby_revisions\Form;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\gatsby_revisions\Entity\GatsbyRevision;
use Drupal\gatsby_revisions\GatsbyRevisionOrchestrator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a confirmation form before clearing out the examples.
 */
class RevertRevision extends ConfirmFormBase {

  /**
   * @var GatsbyRevisionOrchestrator
   */
  protected $gatsbyRevisionOrchestrator;

  /**
   * @var EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * RevertRevision constructor.
   *
   * @param GatsbyRevisionOrchestrator $gatsby_revision_orchestrator
   */
  public function __construct(GatsbyRevisionOrchestrator $gatsby_revision_orchestrator, EntityTypeManagerInterface $entity_type_manager) {
    $this->gatsbyRevisionOrchestrator = $gatsby_revision_orchestrator;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('gatsby_revision.orchestrator'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'gatsby_revisions_revert_revision';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to do this?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.gatsby_revision.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $gatsby_revision = \Drupal::request()->attributes->get('gatsby_revision');

    /** @var GatsbyRevision $gatsby_revision */
    $gatsby_revision = $this->entityTypeManager->getStorage('gatsby_revision')->load($gatsby_revision);

    $response = $this
      ->gatsbyRevisionOrchestrator
      ->revert($gatsby_revision->get('gatsby_revision_number')->value);

    $this->messenger()->addStatus($response->message);
    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}