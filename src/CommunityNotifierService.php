<?php

namespace Drupal\community_notifier;
use Drupal\community_notifier\Entity\CommunityNotifierFrequency;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\flag\FlagService;

/**
 * Class CommunityNotifierService.
 */
class CommunityNotifierService implements CommunityNotifierServiceInterface {

  /**
   * Drupal\flag\FlagService definition.
   *
   * @var \Drupal\flag\FlagService
   */
  private $flag;
  private $currentUser;
  /*
   * @var EntityTypeManagerInterface
   * */
  private $entityTypeManager;
  /**
   * Constructs a new CommunityNotifierService object.
   */
  public function __construct(FlagService $flag, AccountInterface $current_user, EntityTypeManagerInterface $entity_type_manager) {
    $this->flag = $flag;
    $this->currentUser = $current_user;
    $this->entityTypeManager = $entity_type_manager;
  }
  public function getFlaggableEntityTypes(){
    $contentFlags = [];
    $flags = $this->flag->getAllFlags();

    foreach ($flags as $flag_key => $flag){
      $flagEnityType = $flag->getFlaggableEntityTypeId();
      if ($flagEnityType == 'node'){
        array_push($contentFlags, $flagEnityType);
      }
    }

    return $contentFlags;
  }
  public function flag($flagId, $flaggedEntityId){
    CommunityNotifierFrequency::create([
      'uid' => $this->currentUser->id(),
      'name' => $this->currentUser->getDisplayName(),
      'flag_id' => $flagId,
      'entity_id' => $flaggedEntityId,
      'entity_name' => $this->getEntityById($flaggedEntityId)->label(),
    ])->save();
  }
  public function unflag($flagId, $flaggedEntityId){
    $notifierEntities = $this->entityTypeManager->getStorage('community_notifier_frequency')->loadMultiple();

    foreach ($notifierEntities as $notifierEntity){
      if ($notifierEntity->getFlagId() == $flagId && $notifierEntity->getFlaggedEntityId() == $flaggedEntityId && $notifierEntity->getOwnerId() == $this->currentUser->id()){
        $this->entityTypeManager->getStorage('community_notifier_frequency')->delete([$notifierEntity]);
      }
    }
  }

  /**
   * @param $flaggedEntityId
   * @return an array of CommunityNotifierFrequency entities with a specified flaggedEntityId.
   */
  public function getNotificationEntities($flaggedEntityId){
    $notifierEntities = $this->entityTypeManager->getStorage('community_notifier_frequency')->loadMultiple();
    $notificationEntities = array();
    foreach ($notifierEntities as $notifierEntity){
      if ($notifierEntity->getFlaggedEntityId() == $flaggedEntityId){
        array_push($notificationEntities, $notifierEntity);
      }
    }

    return $notificationEntities;
  }
  public function getEntityById($nodeId, $enityType = 'node'){
    $entities = $this->entityTypeManager->getStorage($enityType)->loadMultiple([$nodeId]);

    return current($entities);
  }
}
