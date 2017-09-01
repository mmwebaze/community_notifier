<?php

namespace Drupal\community_notifier;

use Symfony\Component\HttpFoundation\Request;
/**
 * Interface CommunityNotifierServiceInterface.
 */
interface CommunityNotifierServiceInterface {

  public function getFlaggableEntityTypes();
  public function flag($flag, $entity, Request $request, array $entities);
  public function unflag($flag, $entity, Request $request, array $entities);
}
