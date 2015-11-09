<?php

/**
 * @file
 * Contains ComstackConversationController
 */

/**
 * ComstackConversationController
 * The Entity controller for Conversation.
 */
class ComstackConversationController extends EntityAPIController {
  protected $uid;

  /**
   * Overrides EntityAPIController::buildQuery().
   *
   * Get extra data when building a query to get Conversations.
   */
  protected function buildQuery($ids, $conditions = array(), $revision_id = FALSE) {
    global $user;
    $uid = NULL;

    // Alter the query conditions to apply UID to the joined table
    $uid = isset($conditions['uid']) ? $conditions['uid'] : $user->uid;
    unset($conditions['uid']);

    $query = parent::buildQuery($ids, $conditions, $revision_id);

    // Get extra data unique to the current user when loading a Conversation.
    $query->join('comstack_conversation_user', 'ccu', 'base.conversation_id = ccu.conversation_id');
    $query->condition('ccu.uid', $uid);
    // @todo Maybe check if this condition exists and alter the query? This stuff will prevent anyone from loading any conversation they're not part of... :/
    $query->fields('ccu', array('uid', 'unread_count', 'delivered', 'muted', 'forwarded', 'starred', 'pinned', 'archived', 'deleted'));

    return $query;
  }

  /**
   * Overrides EntityAPIController::invoke()
   *
   * Override the invoke method to also try and find hook methods on this
   * class.
   */
  public function invoke($hook, $entity) {
    // Run the usual hooks and so on.
    parent::invoke($hook, $entity);

    // Invoke any methods within this controller.
    $method = 'hook' . ucfirst($hook);
    if (method_exists($this, $method)) {
      $this->$method($entity);
    }
  }

  /**
   * Overrides EntityAPIController::delete()
   *
   * When deleting a conversation, just mark it as deleted.
   *
   * @see $this->permanentlyDelete()
   */
  public function delete($ids, DatabaseTransaction $transaction = NULL) {
    try {
      db_update('comstack_conversation_user')
        ->fields(array(
          'deleted' => 1,
        ))
        ->condition('conversation_id', $ids, 'IN')
        ->execute();

      // Clear dat cache.
      $this->resetCache($ids);

      db_ignore_slave();
    }
    catch (Exception $e) {
      if (isset($transaction)) {
        $transaction->rollback();
      }
      watchdog_exception($this->entityType, $e);
      throw $e;
    }
  }

  /**
   * Permanently delete a conversation.
   */
  public function permanentlyDelete($ids, DatabaseTransaction $transaction = NULL) {
    parent::delete($ids, $transaction);
  }

  /**
   * Glue together a data array to be used by the hook methods in this class.
   *
   * @return array
   */
  public function getUserConversationProperties(ComstackConversation $conversation) {
    return array(
      'muted' => isset($conversation->muted) ? $conversation->muted : 0,
      'forwarded' => isset($conversation->forwarded) ? $conversation->forwarded : 0,
      'starred' => isset($conversation->starred) ? $conversation->starred : 0,
      'pinned' => isset($conversation->pinned) ? $conversation->pinned : 0,
      'archived' => isset($conversation->archived) ? $conversation->archived : 0,
      'deleted' => isset($conversation->deleted) ? $conversation->deleted : 0,
    );
  }

  /**
   * When a new conversation is saved, create the user specific data for all
   * participants.
   */
  public function hookInsert(ComstackConversation $conversation) {
    global $user;
    $fields = $this->getUserConversationProperties($conversation);

    foreach ($conversation->getParticipants() as $uid) {
      $fields['conversation_id'] = $conversation->conversation_id;
      $fields['uid'] = $uid;

      db_insert('comstack_conversation_user')
        ->fields($fields)
        ->execute();
    }
  }

  /**
   * Update the user specific data for a conversation participant.
   */
  public function hookUpdate(ComstackConversation $conversation) {
    $this->saveConversationUserData($conversation);
  }

  /**
   * Save the aspects of data unique to a user for a conversation. This method
   * allows us to only do the minimum of database interactions.
   */
  public function saveConversationUserData(ComstackConversation $conversation) {
    $fields = $this->getUserConversationProperties($conversation);

    db_update('comstack_conversation_user')
      ->fields($fields)
      ->condition('conversation_id', $conversation->conversation_id)
      ->condition('uid', $conversation->uid)
      ->execute();
    $this->resetCache(array($conversation->conversation_id));
  }

  /**
   * Set a conversations unread count.
   */
  public function setConversationUnreadCount(ComstackConversation $conversation, $count) {
    if (!ctype_digit((string) $count)) {
      throw new \ComstackInvalidParameterException(t("You can't set a conversations unread count to that, it's not a number."));
    }

    db_update('comstack_conversation_user')
      ->fields(array('unread_count' => $count))
      ->condition('conversation_id', $conversation->conversation_id)
      ->condition('uid', $conversation->uid)
      ->execute();
    $this->resetCache(array($conversation->conversation_id));
  }
}
