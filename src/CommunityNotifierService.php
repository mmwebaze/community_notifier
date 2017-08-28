<?php

namespace Drupal\community_notifier;
use Drupal\community_notifier\Entity\CommunityNotifierFrequency;
use Drupal\community_notifier\Util\CommunityNotifierUtility;
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
    $storage = $this->entityTypeManager->getStorage('community_notifier_frequency');
    $ids = $storage->getQuery()
      ->condition('entity_id', $flaggedEntityId, '=')
      ->condition('flag_id', $flagId, '=')
      ->condition('user_id', $this->currentUser->id(), '=')
      ->execute();

    foreach ($storage->loadMultiple($ids) as $entity){
      $entity->delete();
    }
  }

  /**
   * @param $flaggedEntityId
   * @return an array of CommunityNotifierFrequency entities with a specified flaggedEntityId.
   */
  public function getNotificationEntitiesById($flaggedEntityId, $ownerId){
    $storage = $this->entityTypeManager->getStorage('community_notifier_frequency');
    $ids = $storage->getQuery()
      ->condition('entity_id', $flaggedEntityId, '=')
      ->condition('user_id', $ownerId, '!=')
      ->execute();
    $notifierEntities = $storage->loadMultiple($ids);

    return $notifierEntities;
  }
  public function getEntityById($nodeId, $enityType = 'node'){
    $entities = $this->entityTypeManager->getStorage($enityType)->loadMultiple([$nodeId]);

    return current($entities);
  }

  /**
   * @param $frequency (immediate, daily or weekly)
   * @return an array of CommunityNotifierFrequency entities with a specified frequency.
   */
  public function getNotificationEntitiesByFrequency($frequency, $condition = '='){

    $storage = $this->entityTypeManager->getStorage('community_notifier_frequency');
    $ids = $storage->getQuery()
      ->condition('frequency', $frequency, $condition)
      ->execute();
    $notifierEntities = $storage->loadMultiple($ids);

    return $notifierEntities;
  }

  /**
   * @param $targetId
   * @param $frequency
   * @return \Drupal\Core\Entity\EntityInterface[]
   */
  public function getCommentsForNotification($targetId, $frequency){
    $range = CommunityNotifierUtility::frequencyDateRange($frequency);
    $storage = $this->entityTypeManager->getStorage('comment');
    $ids = $storage->getQuery()
      ->condition('entity_id', $targetId, '=')
      ->condition('created', $range, 'BETWEEN')
      ->range(0, 5)
      ->execute();
    $comments = $storage->loadMultiple($ids);

    return $comments;
  }

  /**
   * @return array of comments subscribed to by different users
   */
  public function getComments($frequency){
    $notificationEntities = $this->getNotificationEntitiesByFrequency($frequency);
    $comments = [];

    foreach ($notificationEntities as $notificationEntity){
      $notificationEntityId = $notificationEntity->id();
      $targetId = $notificationEntity->getFlaggedEntityId();
      //$frequency = $notificationEntity->getFrequency();
      $notificationComments = $this->getCommentsForNotification($targetId, $frequency);
      $tempComments = [];
      $email = $notificationEntity->getOwner()->getEmail();
      $notifiableComments = [];
      $subject = '';
      foreach ($notificationComments as $notificationComment){
        $subject = $notificationComment->getCommentedEntity()->label();
        $temp = [];
        //$temp['subject'] = $notificationComment->getSubject();
        $temp['body'] = $notificationComment->get('comment_body')->value;
        $temp['created'] = date('Y-m-d', $notificationComment->get('created')->value);
        array_push($notifiableComments, $temp);
      }
      if (!empty($notifiableComments)){
        $comments[$notificationEntityId] = [
          'email' => $email,
          'subject' => $subject,
          'comments' => $notifiableComments
        ];
      }

      /*if (!empty($notificationComments)){
        $comments[$notificationEntityId] = [
          'frequency' => $frequency,
          'email' => $notificationEntity->getOwner()->getEmail(),
          //'comments' => $this->getCommentsForNotification($targetId, $frequency),
          'comments' => $tempComments,
        ];
      }*/
    }
    return $comments;
  }
}
