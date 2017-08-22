<?php

namespace Drupal\community_notifier;
use Drupal\community_notifier\Entity\CommunityNotifierFrequency;
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
  protected $flag;
  private $currentUser;
  /**
   * Constructs a new CommunityNotifierService object.
   */
  public function __construct(FlagService $flag, AccountInterface $current_user) {
    $this->flag = $flag;
    $this->currentUser = $current_user;
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
    //drupal_set_message($flag.' unflag in community notifier '.$entity);
    CommunityNotifierFrequency::create([
      'uid' => $this->currentUser->id(),
      'label' => '',
      'flag_id' => $flagId,
      'entity_id' => $flaggedEntityId,
    ])->save();
  }
  public function unflag($flag, $entity){

  }
}
