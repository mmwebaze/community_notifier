<?php

namespace Drupal\community_notifier\Controller;

use Drupal\community_notifier\CommunityNotifierServiceInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\flag\Controller\ActionLinkController;
use Drupal\flag\FlagInterface;
use Drupal\flag\FlagServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CommunityNotifierController.
 */
class CommunityNotifierController extends ActionLinkController {

  private $communityNotifier;

  public function __construct(CommunityNotifierServiceInterface $communityNotifier, FlagServiceInterface $flag, RendererInterface $renderer) {
    parent::__construct($flag, $renderer);
    $this->communityNotifier = $communityNotifier;
  }
  /**
   * {@inheritdoc}
   */
  public function flag(FlagInterface $flag, $entity_id, Request $request) {
    $nodes = $this->communityNotifier->getForumTopics($entity_id);
    $this->communityNotifier->flag($flag->id(), $entity_id, $request, $nodes);
    return parent::flag($flag, $entity_id, $request);
  }
  /**
   * {@inheritdoc}
   */
  public function unflag(FlagInterface $flag, $entity_id, Request $request) {
    $nodes = $this->communityNotifier->getForumTopics($entity_id);
    $this->communityNotifier->unflag($flag->id(), $entity_id, $request, $nodes);
    return parent::unflag($flag, $entity_id, $request);
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('community_notifier.nodeflags'),
      $container->get('flag'),
      $container->get('renderer')
    );
  }
}
