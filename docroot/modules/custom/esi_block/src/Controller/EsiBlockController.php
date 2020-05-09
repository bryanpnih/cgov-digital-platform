<?php

namespace Drupal\esi_block\Controller;

use Drupal\block\Entity\Block;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Render\RenderContext;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for rendering navigation blocks.
 */
class EsiBlockController extends ControllerBase {

  /**
   * Drupal renderer Service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Drupal Entity Type Manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function getModuleName() {
    return 'esi_block';
  }

  /**
   * Constructs an LanguageBar object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Drupal Entity Manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   Drupal Renderer Service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, RendererInterface $renderer) {
    $this->entityTypeManager = $entityTypeManager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * Gets the block configuration entity and renders it out.
   *
   * @param \Drupal\block\Entity\Block $block
   *   The the block entity to render.
   */
  public function view(Block $block) {

    if ($block == NULL) {
      throw new NotFoundHttpException();
    }

    // Create a RenderContext to capture any BubbleableMetadata.
    // All controllers execution are are wrapped with their own RenderContext
    // in EarlyRenderingControllerWrapperSubscriber::wrapControllerExecutionInRenderContext
    // If any thing you do Bubbles up metadata to THAT context and you return a
    // CacheableResponse then an exception will be thrown. There are actually
    // MANY things that bubble up metadata. (e.g. some entity queries, some URL
    // generation, rendering to name a few). NOTE: renderRoot will handle this
    // for you.
    // So we will need to wrap all this block work in our own context.
    $context = new RenderContext();

    $metadata = new CacheableMetadata();

    // Note: use (...) is PHP's crappy closure support, you have to
    // declare what vars will be available. AND you can't use ($this).
    $that = $this;
    $renderedString = $this->renderer->executeInRenderContext($context, function () use ($that, $block, &$metadata) {
      /* @var \Drupal\Core\Entity\EntityViewBuilderInterface */
      $view_builder = $that->entityTypeManager->getViewBuilder('block');

      // Build the block's render array.
      $build = $view_builder->view($block);

      // Add metadata from our build object to the context.
      $metadata = CacheableMetadata::createFromRenderArray($build);

      return $that->renderer->renderRoot($build);
    });

    // Now we setup a CacheableResponse which should be able to take in
    // cache metadata and generate the correct HTML tags.
    $response = new CacheableResponse();

    // Set the rendered content.
    $response->setContent($renderedString);

    // We should probably add a context for the ESI rendering. Although
    // maybe it should just be a view mode.
    $response->addCacheableDependency($metadata);

    // Return the response so it can be returned to the client.
    return $response;
  }

}
