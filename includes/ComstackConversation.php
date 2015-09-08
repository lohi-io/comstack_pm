<?php

/**
 * @file
 * Contains ComstackConversation
 */

/**
 * ComstackConversation
 * The Entity class for a Conversation.
 */
class ComstackConversation extends Entity {
  /**
   * Quick access to the current user id, instead of global'in.
   *
   * @var integer
   */
  private $current_uid;

  /**
   * Wrapper variable for this conversation.
   *
   * @var object
   */
  private $wrapper;

  /**
   * Set things up!
   *
   * @see Entity::__construct()
   */
  public function __construct(array $values = array()) {
    parent::__construct($values, 'comstack_conversation');

    global $user;
    $this->current_uid = $user->uid;
    $this->wrapper = entity_metadata_wrapper($this->entityType, $this);
  }

  /**
   * Kick of the actions!
   */

  /**
   * Add a new reply/message to this conversation, and update the relevant meta
   * data on this conversation. Also trigger notifications and whatnot.
   *
   * @return boolean
   * @throws \ComstackInvalidParameterException
   */
  public function reply($text, $uid = NULL) {
    // Prevent empty or invalid values being passed in.
    if (empty($text) || !is_string($text)) {
      throw new \ComstackInvalidParameterException(t("The reply you're attempting to post is invalid, something's wrong. It's either empty or not text."));
    }
    if (!empty($uid) && !ctype_digit((string) $uid)) {
      throw new \ComstackInvalidParameterException(t("The reply you're trying to create on behalf of someone else has gone wrong, the user ID passed isn't valid."));
    }

    // Work out the user id.
    if (!$uid) {
      $uid = $this->current_uid;
    }
    $account = user_load($uid);

    // Create a new message.
    $message = message_create('cs_pm', array(), $account);
    $message_wrapper = entity_metadata_wrapper('message', $message);

    // Set data on the message.
    $message_wrapper->cs_pm_conversation->set($this->conversation_id);
    $message_wrapper->cs_pm_text->set(array(
      'value' => $text,
      'format' => variable_get('comstack_pm_input_format', 'cs_pm'),
    ));
    $message_wrapper->timestamp->set(REQUEST_TIME);

    // Set a flag.
    $message->is_reply = TRUE;

    // Save the message.
    $message_wrapper->save();

    if ($message) {
      // Update the conversations meta information, fire notifications and
      // unread counts.

      // Set this conversations "last updated by" and "updated" fields.
      $this->wrapper->cs_pm_last_updated_by->set($uid);
      $this->wrapper->updated->set(REQUEST_TIME);
      $this->save();

      // Update user specific data.
      foreach ($this->getParticipants() as $participant_uid) {
        if ($uid != $participant_uid) {
          // Update this conversations unread count.


          // Trigger notification.
          db_query('UPDATE {comstack_conversation_user} SET unread_count = unread_count + 1 WHERE conversation_id = :conversation_id AND uid = :uid', array(':conversation_id' => $this->conversation_id, ':uid' => $participant_uid));
        }
      }
    }

    // Return the saved message :3
    return $message;
  }

  /**
   * Add an event to the conversation. This could be something like a new user
   * joining, or an existing one leaving. Could also be reactions or non-text
   * responses to a conversation like a sticker.
   */
  public function addEvent() {
  }

  /**
   * Return the user account of the current user.
   *
   * @return object
   */
  public function getAccount() {
    return user_load($this->current_uid);
  }

  /**
   * Return an EntityFieldQuery object ready for use when needing a list of
   * messages within this conversation.
   *
   * @return \EntityFieldQuery
   */
  public function getMessagesQuery($sort = 'DESC') {// @todo
    $uid = $this->current_uid;
    $account = $this->getAccount();

    // Build up a new query.
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'message')
      ->entityCondition('bundle', 'cs_pm')
      ->fieldCondition('cs_pm_conversation', 'target_id', $this->conversation_id)
      ->propertyOrderBy('timestamp', $sort);

    // Add in extra conditions dependant on user permissions.
    if (!user_access('view deleted comstack messages', $account)) {
      $query->propertyCondition('deleted', 0);
    }

    // We loop in transcripts via hook_query_tag alter.
    $query->addTag('comstack_pm_separate_transcripts');

    return $query;
  }

  /**
   * Get a list of IDs of messages in this conversation for the current user.
   * This should take in to account that if set to, each user will have a
   * separate version history of the conversation message history to another.
   *
   * @param $sort
   *   Order in which to sort messages within this conversation. Will accept
   *   either 'ASC' or 'DESC'. Defaults to 'DESC'
   *
   * @return array
   *   An array of Message IDs.
   */
  public function getMessages($sort = 'DESC') {
    $sort = $sort === 'DESC' ? 'DESC' : 'ASC';
    $query = $this->getMessagesQuery($sort);
    $result = $query->execute();

    return isset($result['message']) ? array_keys($result['message']) : array();
  }

  /**
   * Get an array of participant user ids.
   *
   * @return array
   */
  public function getParticipants() {
    $participants = array();

    if ($this->wrapper->cs_pm_participants->value()) {
      foreach ($this->wrapper->cs_pm_participants->getIterator() as $delta => $user_wrapper) {
        $participants[] = $user_wrapper->uid->value();
      }
    }

    return $participants;
  }

  /**
   * Check if a particular user is a current participant in this conversation.
   *
   * @return boolean
   * @throws \ComstackInvalidParameterException
   */
  public function userIsAParticipant($uid) {
    if (!ctype_digit((string) $uid)) {
      throw new \ComstackInvalidParameterException(t("When checking that a user is a participant in a conversation you must pass in an integer."));
    }

    return in_array($uid, $this->getParticipants());
  }

  /**
   * Join this conversation.
   */
  public function join() {
    if (!$this->userIsAParticipant($this->current_uid)) {
      $this->wrapper->cs_pm_participants[] = $this->current_uid;
      $this->wrapper->cs_pm_historical_participants[] = $this->current_uid;

      // Save this conversation.
      $this->save();
    }
  }

  /**
   * Leave/remove the current user from this conversation.
   */
  public function leave() {
    if ($this->userIsAParticipant($this->current_uid)) {
      $participants = $this->getParticipants();

      foreach ($participants as $k => $uid) {
        if ($this->current_uid = $uid) {
          unset($participants[$k]);
        }
      }

      $this->wrapper->cs_pm_participants = $participants;
      $this->save();
    }
  }

  /**
   * Invite other users to this conversation.
   */
  public function invite(array $ids) {
    foreach ($ids as $uid) {
      if (!ctype_digit((string) $uid)) {
        throw new \ComstackInvalidParameterException(t("Failed to invite a user to a conversation because that's not a valid user ID."));
      }

      if (!$this->userIsAParticipant($uid)) {
        $this->wrapper->cs_pm_participants[] = $uid;
        $this->wrapper->cs_pm_historical_participants[] = $uid;
      }

      // Save this conversation.
      $this->save();
    }
  }

  /**
   * Delete messages from this conversation, or depending on settings, messages
   * from this users version of the conversation message history. Only grab
   * messages that the current user authored.
   *
   * @return boolean
   */
  public function deleteMessages(array $ids) {
    // Check that this user has permission to delete messages.
    if (user_access('delete own comstack conversation messages')) {
      return FALSE;
    }

    // Get the EFQ for messages.
    $query = $this->getMessagesQuery($sort);
    $query->propertyCondition('uid', $this->current_uid);
    $query->propertyCondition('deleted', 0);
    $result = $query->execute();

    $available_ids = isset($result['message']) ? array_keys($result['message']) : array();
    $record_transcripts = variable_get('comstack_pm_record_separate_transcripts', FALSE);

    // If we've got messages we can delete, do it.
    if ($available_ids) {
      if ($record_transcripts) {
        db_update('comstack_conversation_message')
          ->fields(array(
            'deleted' => 1,
          ))
          ->condition('mid', $available_ids, 'IN')
          ->condition('uid', $this->current_uid)
          ->execute();
      }
      else {
        db_update('message')
          ->fields(array(
            'deleted' => 1,
          ))
          ->condition('mid', $available_ids, 'IN')
          ->execute();
      }
    }

    return FALSE;
  }

  /**
   * Set the title of this conversation, expects that permission checking has
   * already been done. Null/'' is an acceptable title.
   */
  public function setTitle($text) {
    $this->wrapper->title->set($text);
    $this->save();
  }

  public function saveConversationUserData() {
    entity_get_controller('comstack_conversation')->saveConversationUserData($this);
  }

  /**
   * Mark this conversation as read.
   */
  public function markAsRead() {
    entity_get_controller('comstack_conversation')->setConversationUnreadCount($this, 0);
  }

  /**
   * Mark this conversation as unread by setting unread messages to "1".
   */
  public function markAsUnread() {
    entity_get_controller('comstack_conversation')->setConversationUnreadCount($this, 1);
  }

  /**
   * Mute this conversation to prevent future notifications hassling the
   * current user.
   */
  public function mute() {
    $this->mute = TRUE;
    $this->saveConversationUserData();
  }

  /**
   * Undo the mute.
   */
  public function unMute() {
    $this->muted = FALSE;
    $this->saveConversationUserData();
  }

  /**
   * Archive this conversation for the current user.
   */
  public function archive() {
    $this->archived = TRUE;
    $this->saveConversationUserData();
  }

  /**
   * Aaaand undo the archive action.
   */
  public function unArchive() {
    $this->archived = FALSE;
    $this->saveConversationUserData();
  }

  /**
   * Pin this conversation.
   */
  public function pin() {
    $this->pinned = TRUE;
    $this->saveConversationUserData();
  }

  /**
   * UnPin this conversation.
   */
  public function unPin() {
    $this->pinned = FALSE;
    $this->saveConversationUserData();
  }

  /**
   * Star this conversation.
   */
  public function star() {
    $this->starred = TRUE;
    $this->saveConversationUserData();
  }

  /**
   * Unstar this conversation.
   */
  public function unStar() {
    $this->starred = FALSE;
    $this->saveConversationUserData();
  }

  /**
   * CRUD operations.
   */

  /**
   * Save this conversation.
   */
  public function save() {
    parent::save();
  }

  public function delete() {
    parent::delete();
  }
}
