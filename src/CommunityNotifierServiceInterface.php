<?php

namespace Drupal\community_notifier;

/**
 * Interface CommunityNotifierServiceInterface.
 */
interface CommunityNotifierServiceInterface {

  public function getFlaggableEntityTypes();
  public function flag($flag, $entity);
  public function unflag($flag, $entity);
}
