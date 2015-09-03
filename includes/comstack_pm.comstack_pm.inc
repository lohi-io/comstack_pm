<?php

/**
 * @file
 * comstack_pm.comstack_pm.inc
 */

/**
 * Implements hook_query_TAG_alter().
 */
function comstack_pm_query_comstack_pm_separate_transcripts_alter(QueryAlterableInterface $query) {
  if (variable_get('comstack_pm_record_separate_transcripts', FALSE)) {
    global $user;

    $query->join('comstack_conversation_message', 'ccm', 'message.mid = ccm.mid');
    $query->condition('ccm.uid', $user->uid);

    if (!user_access('view deleted comstack messages')) {
      $query->condition('ccm.deleted', 0);
    }
  }
}

/**
 * Implements hook_entity_presave().
 *
 * When a message is updated record edits and changes to text, if not new.
 */
function comstack_pm_message_presave($message) {
  if ($message->type === 'cs_pm') {
    $wrapper = entity_metadata_wrapper('message', $message);
    // Set the updated time.
    $message->updated = REQUEST_TIME;

    if (!$message->is_new && variable_get('comstack_pm_record_message_edits', FALSE)) {
      // Compare the original text to the new text, if altered record the old
      // text and timestamp and increment the "edits" property on this message
      // entity.
    }
  }
}

/**
 * Add an entry to the message edits table.
 */
function comstack_pm_message_record_text($message) {
  $wrapper = entity_metadata_wrapper('message', $message);
  extract($wrapper->cs_pm_text->value());

  db_insert('comstack_message_edits')
    ->fields(array(
      'mid' => $message->mid,
      'timestamp' => REQUEST_TIME,
      'text' => $value,
      'format' => $format,
    ))
    ->execute();
}

/**
 * Implements hook_entity_insert().
 *
 * When a message is created add it to conversation message transcripts if
 * keeping separate version per participant.
 */
function comstack_pm_message_insert($message) {
  if ($message->type === 'cs_pm') {
    $wrapper = entity_metadata_wrapper('message', $message);
    $conversation_id = $wrapper->cs_pm_conversation->getIdentifier();

    // Chuck in the first message history entry.
    if (variable_get('comstack_pm_record_message_edits', FALSE)) {
      comstack_pm_message_record_text($message);
    }

    // Load the conversation this message belongs to.
    if (!$conversation = comstack_conversation_load($conversation_id, $message->uid)) {
      return;
    }

    // Check we're meant to record separate histories.
    if (variable_get('comstack_pm_record_separate_transcripts', FALSE)) {
      $participants = $conversation->getParticipants();

      // Add an entry to each users conversation history.
      foreach ($participants as $uid) {
        db_insert('comstack_conversation_message')
          ->fields(array(
            'conversation_id' => $conversation_id,
            'uid' => $uid,
            'mid' => $message->mid,
          ))
          ->execute();
      }
    }
  }
}

/**
 * Implements hook_entity_update().
 *
 */
function comstack_pm_message_update($message) {
  if ($message->type === 'cs_pm') {
    // If we're recording edits, save stuff.
    if (variable_get('comstack_pm_record_message_edits', FALSE)) {
      $wrapper = entity_metadata_wrapper('message', $message);
      $old_wrapper = entity_metadata_wrapper('message', $message->original);

      // Check that the text has changed.
      $array_diff = array_diff($wrapper->cs_pm_text->value(), $old_wrapper->cs_pm_text->value());
      if (!empty($array_diff)) {
        comstack_pm_message_record_text($message);
        db_query('UPDATE {message} SET edits = edits + 1 WHERE mid = :mid', array(':mid' => $message->mid));
      }
    }
  }
}

/**
 * Create a conversation.
 *
 * @param $data
 *   The data to start a new conversation with.
 *   - participants (required) An array of user IDs.
 *   - title (optional) A string to title the conversation with.
 *   - text (required) The text of the first message in the conversation.
 *   - uid (optional) The ID of the user who started this conversation.
 *
 * @return boolean
 */
function comstack_pm_new_conversation($data) {
  if (empty($data['participants']) || !is_array($data['participants']) || empty($data['text']) || !is_string($data['text'])) {
    throw new \ComstackInvalidParameterException(t('Attempted to create a new conversation with either incomplete or incorrect data.'));
  }

  // Create the conversation.
  global $user;
  $uid = isset($data['uid']) && ctype_digit((string) $data['uid']) ? (integer) $data['uid'] : $user->uid;
  $conversation = entity_create('comstack_conversation', array());

  // Wrap the entity and start setting data.
  $conversation->uid = $uid;
  $conversation->started = REQUEST_TIME;
  $conversation->updated = REQUEST_TIME;
  if (!empty($data['title'])) {
    $conversation->title = $data['title'];
  }

  $wrapper = entity_metadata_wrapper('comstack_conversation', $conversation);
  $wrapper->cs_pm_participants->set($data['participants']);
  $wrapper->cs_pm_historical_participants->set($data['participants']);
  $wrapper->cs_pm_started_by->set($uid);
  $wrapper->cs_pm_last_updated_by->set($uid);

  // Save the conversation.
  $conversation->save();

  // Create the first message.
  $conversation->reply($data['text'], $uid);
}
