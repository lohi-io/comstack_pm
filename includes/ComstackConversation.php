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
  protected $wrapper;

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
   * Kick off the actions!
   */

  /**
   * Set the current user id.
   */
  public function setCurrentUser($account) {
    $this->current_uid = $account->uid;
  }

  /**
   * Add a new reply/message to this conversation, and update the relevant meta
   * data on this conversation. Also trigger notifications and whatnot.
   *
   * @return boolean
   * @throws \ComstackInvalidParameterException
   */
  public function reply($text, $uid = NULL, $timestamp = NULL) {
    // Prevent empty or invalid values being passed in.
    if (!is_string($text) || $text == '') {
      throw new \ComstackInvalidParameterException(t("The reply you're attempting to post is invalid, something's wrong. It's either empty or not text."));
    }
    if (!empty($uid) && !ctype_digit((string) $uid)) {
      throw new \ComstackInvalidParameterException(t("The reply you're trying to create on behalf of someone else has gone wrong, the user ID passed isn't valid."));
    }

    // Work out the user id.
    if (!$uid) {
      $uid = $this->current_uid;
    }
    else {
      $this->setCurrentUser(user_load($uid));
    }
    $account = user_load($uid);

    // Check that the current user is part of the conversation.
    if (!$this->userIsAParticipant()) {
      throw new \ComstackPMInactiveParticipantException();
    }

    // Check that the conversation can be replied to.
    if (!$this->checkForActiveParticipants()) {
      throw new \ComstackPMNoOtherParticipantsException();
    }

    // Create a new message.
    $message = message_create('cs_pm', array(), $account);
    $message_wrapper = entity_metadata_wrapper('message', $message);

    // Set data on the message.
    $message_wrapper->cs_pm_conversation->set($this->conversation_id);
    $message_wrapper->cs_pm_text->set(array(
      'value' => $text,
      'format' => variable_get('comstack_pm_input_format', 'cs_pm'),
    ));
    $timestamp = $timestamp ? $timestamp : REQUEST_TIME;
    $message_wrapper->timestamp->set($timestamp);

    // Set a flag.
    $message->is_reply = TRUE;

    // Save the message.
    $message_wrapper->save();

    if ($message) {
      // Update the conversations meta information, fire notifications and
      // unread counts.
      $invoke_reply_hook = $this->started != $timestamp;

      // Set this conversations "last updated by" and "updated" fields.
      $this->wrapper->cs_pm_last_updated_by->set($uid);
      $this->wrapper->updated->set($timestamp);
      $this->wrapper->cs_pm_last_message->set($message->mid);
      $this->save();

      // Update user specific data.
      foreach ($this->getParticipants(TRUE) as $participant_uid) {
        // Update this conversations unread count.
        db_query('UPDATE {comstack_conversation_user} SET unread_count = unread_count + 1 WHERE conversation_id = :conversation_id AND uid = :uid', array(':conversation_id' => $this->conversation_id, ':uid' => $participant_uid));

        // Trigger notifications.
      }

      if ($invoke_reply_hook) {
        module_invoke_all('comstack_conversation_reply', $this, $message, $account);
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
   * @todo think about blocked users :/
   * @return array
   */
  public function getParticipants($omit_current = FALSE) {
    $participants = array();

    if ($this->wrapper->cs_pm_participants->value()) {
      foreach ($this->wrapper->cs_pm_participants->getIterator() as $delta => $user_wrapper) {
        $id = $user_wrapper->getIdentifier();
        if (!$omit_current || $omit_current && $this->current_uid != $id) {
          $participants[] = $id;
        }
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
  public function userIsAParticipant($uid = NULL) {
    if (!$uid) {
      $uid = $this->current_uid;
    }

    if (!ctype_digit((string) $uid)) {
      throw new \ComstackInvalidParameterException(t('When checking that a user is a participant in a conversation you must pass in an integer.'));
    }

    return in_array($uid, $this->getParticipants());
  }

  /**
   * Check that this conversation has other active participants.
   *
   * @return boolean
   */
  public function checkForActiveParticipants() {
    $participants = $this->getParticipants(TRUE);

    if (empty($participants)) {
      return FALSE;
    }

    $unavailable = array();
    foreach ($participants as $participant) {
      try {
        comstack_pm_validate_recipients(array($participant), $this->current_uid);
      }
      catch (\ComstackUnavailableUserException $e) {
        $unavailable[] = $participant;
        break;
      }
    }

    return count($participants) > 0 && count($unavailable) < count($participants);
  }

  /**
   * Join this conversation.
   */
  public function join($user_id = NULL) {
    $target_user = $user_id ? $user_id : $this->current_uid;

    if (!$this->userIsAParticipant($target_user)) {
      $this->wrapper->cs_pm_participants[] = $target_user;

      if (!in_array($target_user, $this->wrapper->cs_pm_historical_participants->value(array('identifier' => TRUE)))) {
        $this->wrapper->cs_pm_historical_participants[] = $target_user;
      }

      // Make sure this conversation isn't marked as deleted for the user.
      if ($target_user == $this->current_uid) {
        $this->deleted = 0;
      }
      else {
        // We'll be updating the other users conversation data.
        db_update('comstack_conversation_user')
          ->fields(array(
            'deleted' => 0,
          ))
          ->condition('conversation_id', $this->conversation_id)
          ->condition('uid', $target_user)
          ->execute();
      }

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
        if ($this->current_uid == $uid) {
          unset($participants[$k]);

          /**
           * When leaving a conversation, if we're keeping separate transcripts
           * and we're set to erase histories on leaving a conversation do it.
           * This is an actual delete rather than marking as deleted because
           * even if it were deleted from this users history, it will still
           * remain in the {message} table. We do this so if it were to be
           * reported we could still access the message.
           */
          if (variable_get('comstack_pm_record_separate_transcripts', FALSE) && variable_get('comstack_pm_on_leave_erase_history', TRUE)) {
            db_delete('comstack_conversation_message')
              ->condition('conversation_id', $this->conversation_id)
              ->condition('uid', $this->current_uid)
              ->execute();
          }

          // Remove the user from comstack_conversation_user.
          db_update('comstack_conversation_user')
            ->fields(array(
              'deleted' => 1,
              'unread_count' => 0
            ))
            ->condition('conversation_id', $this->conversation_id)
            ->condition('uid', $this->current_uid)
            ->execute();

          break;
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
        throw new \ComstackInvalidParameterException(t("Failed to invite a user to a conversation they're already part of."));
      }

      try {
        comstack_pm_validate_recipients(array($uid), $account);
      }
      catch (ComstackUnavailableUserException $e) {
        throw $e;
      }

      $this->wrapper->cs_pm_participants[] = $uid;
      $this->wrapper->cs_pm_historical_participants[] = $uid;

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
      // Update the transcript for this user.
      if ($record_transcripts) {
        db_update('comstack_conversation_message')
          ->fields(array(
            'deleted' => 1,
          ))
          ->condition('mid', $available_ids, 'IN')
          ->condition('uid', $this->current_uid)
          ->execute();
      }

      // Aand update the message itself, we do this because if separate
      // transcripts are on, any other participants will still have the
      // message. If a new person joins and comstack_pm is set to inherit
      // a history of previously sent messages, all the ones not marked as
      // deleted will be used.
      db_update('message')
        ->fields(array(
          'deleted' => 1,
        ))
        ->condition('mid', $available_ids, 'IN')
        ->execute();
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

    // Fire a hook.
    $current_user = user_load($this->current_uid);
    module_invoke_all('comstack_conversation_mark_as_read', $this, $current_user);
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
