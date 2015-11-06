<?php

/**
 * @file
 * comstack_pm.entity.inc
 */

/**
 * Implements hook_entity_info().
 */
function comstack_pm_entity_info() {
  $entities = array();

  $entities['comstack_conversation'] = array(
    'label' => t('Comstack Conversation'),
    'plural label' => t('Comstack Conversations'),
    // Don't use the default DrupalDefaultEntityController class.
    'controller class' => 'ComstackConversationController',
    'entity class' => 'ComstackConversation',
    'base table' => 'comstack_conversation',
    'fieldable' => TRUE,
    'entity keys' => array(
      'id' => 'conversation_id',
      'label' => 'title',
    ),
    'module' => 'comstack_pm',
    'entity cache' => FALSE,
    'metatags' => FALSE,
    'view modes' => array(
      'full' => array(
        'label' => t('Full'),
        'custom settings' => FALSE,
      ),
    ),
    'access callback' => 'comstack_pm_conversation_access',
  );

  return $entities;
}

/**
 * Implements hook_entity_property_info_alter().
 */
function comstack_pm_entity_property_info_alter(&$info) {
  // Conversation entity.
  $conversation_properties = &$info['comstack_conversation']['properties'];

  $conversation_properties['conversation_id'] = array(
    'label' => t("Conversation ID"),
    'type' => 'integer',
    'description' => t("The unique ID of the conversation."),
    'schema field' => 'conversation_id',
  );
  $conversation_properties['started'] = array(
    'label' => t('Conversation started'),
    'type' => 'date',
    'description' => t('The date the conversation was created.'),
    'setter callback' => 'entity_property_verbatim_set',
    'setter permission' => 'administer comstack content',
    'schema field' => 'started',
  );
  $conversation_properties['updated'] = array(
    'label' => t('Conversation last updated'),
    'type' => 'date',
    'description' => t('The date the conversation was most recently updated.'),
    'setter callback' => 'entity_property_verbatim_set',
    'setter permission' => 'administer comstack content',
    'schema field' => 'updated',
  );
  $conversation_properties['title'] = array(
    'label' => t('Title'),
    'description' => t('The title of the conversation.'),
    'setter callback' => 'entity_property_verbatim_set',
    'schema field' => 'title',
  );
  $conversation_properties['uid'] = array(
    'label' => t('Author'),
    'type' => 'user',
    'description' => t('The creator of the conversation.'),
    'setter callback' => 'entity_property_verbatim_set',
    'setter permission' => 'administer comstack content',
    'required' => TRUE,
    'schema field' => 'uid',
  );
  $conversation_properties['unread_count'] = array(
    'label' => t('Unread count'),
    'description' => t('Number of unread messages in the conversation.'),
    'type' => 'integer',
    'setter callback' => 'entity_property_verbatim_set',
    'schema field' => 'unread_count',
  );
  $conversation_properties['muted'] = array(
    'label' => t('Muted'),
    'description' => t('Whether updates in this conversation should alert the user.'),
    'setter callback' => 'entity_property_verbatim_set',
    'setter permission' => 'administer comstack content',
    'schema field' => 'muted',
    'type' => 'boolean',
  );
  $conversation_properties['forwarded'] = array(
    'label' => t('Forwarded'),
    'description' => t('Whether or not this user has forwarded this conversation to someone else.'),
    'setter callback' => 'entity_property_verbatim_set',
    'setter permission' => 'administer comstack content',
    'schema field' => 'forwarded',
    'type' => 'boolean',
  );
  $conversation_properties['starred'] = array(
    'label' => t('Starred'),
    'description' => t('Boolean indicating if this user has starred this conversation.'),
    'setter callback' => 'entity_property_verbatim_set',
    'setter permission' => 'administer comstack content',
    'schema field' => 'starred',
    'type' => 'boolean',
  );
  $conversation_properties['pinned'] = array(
    'label' => t('Pinned'),
    'description' => t('Whether the conversation should be displayed at the top of lists in which it appears.'),
    'setter callback' => 'entity_property_verbatim_set',
    'setter permission' => 'administer comstack content',
    'schema field' => 'pinned',
    'type' => 'boolean',
  );
  $conversation_properties['archived'] = array(
    'label' => t('Archived'),
    'description' => t('Whether the conversation has been archived.'),
    'setter callback' => 'entity_property_verbatim_set',
    'setter permission' => 'administer comstack content',
    'schema field' => 'archived',
    'type' => 'boolean',
  );
  $conversation_properties['deleted'] = array(
    'label' => t('Deleted'),
    'description' => t('Whether the conversation has been deleted.'),
    'setter callback' => 'entity_property_verbatim_set',
    'setter permission' => 'administer comstack content',
    'schema field' => 'deleted',
    'type' => 'boolean',
  );
}

/**
 * Custom entity getter callback used from metadata info.
 */
function comstack_pm_conversation_get_entity($data, array $options, $name, $type, $info) {
  return entity_load_single($data->entity_type, $data->entity_id);
}

/**
 * Determine if a user can access a conversation.
 */
function comstack_pm_conversation_access($op, $entity, $account, $entity_type = 'comstack_conversation') {
  // If the $account var is null  then assume it's the current user.
  if (!$account) {
    global $user;
    $account = $user;
  }

  // Administer permission should allow access to all.
  if (user_access('bypass comstack_pm access checks', $account)) {
    return TRUE;
  }

  // Creating a new conversation doesn't require any other involvement, just a
  // permission.
  if ($op === 'create') {
    return user_access('start new comstack conversations', $account);
  }

  // Wrap the entity and check that the user is a participant in the
  // conversation.
  $wrapper = entity_metadata_wrapper($entity_type, $entity);
  $is_a_participant = FALSE;
  if ($wrapper && $wrapper->cs_pm_participants->value()) {
    foreach ($wrapper->cs_pm_participants->getIterator() as $delta => $user_wrapper) {
      if ($user_wrapper->getIdentifier() == $account->uid) {
        // Found!
        $is_a_participant = TRUE;
      }
    }
  }

  // Check the other operations.
  if ($is_a_participant) {
    // Update operations relate to things like setting a conversation title,
    // marking a conversation as read and so on, if they're part of the
    // conversation then that's fine.
    if ($op === 'view' || $op === 'update') {
      return TRUE;
    }
    elseif ($op === 'delete' && user_access('delete leave comstack conversation', $account)) {
      return TRUE;
    }
  }

  return FALSE;
}

/**
 * Determine if a user can access a comstack private messaging message entity.
 */
function comstack_pm_message_access($op, $entity, $account) {
  if (!$account) {
    global $user;
    $account = $user;
  }

  // Allow admin to bypass checks.
  if (user_access('bypass comstack_pm access checks', $account)) {
    return TRUE;
  }

  // Read access is determined by are you part of this conversation?
  switch ($op) {
    case 'view':
    case 'create':
      $wrapper = entity_metadata_wrapper('message', $entity);
      $conversation = $wrapper->cs_pm_conversation->value();

      if ($conversation && comstack_pm_conversation_access('view', $conversation, $account)) {
        return TRUE;
      }
      break;
    case 'update':
      if ($account->uid == $entity->uid && user_access('edit own comstack conversation messages', $account)) {
        return TRUE;
      }
      break;
    case 'delete':
      if ($account->uid == $entity->uid && user_access('delete own comstack conversation messages', $account)) {
        return TRUE;
      }
      break;
  }

  return FALSE;
}

/**
 * Load a conversation.
 */
function comstack_conversation_load($conversation_id, $uid = NULL, $reset = FALSE) {
  $conversations = comstack_conversation_load_multiple(array($conversation_id), $uid, array(), $reset);
  return reset($conversations);
}

/**
 * Load a series of conversations.
 */
function comstack_conversation_load_multiple(array $conversation_ids = array(), $uid = NULL, array $conditions = array(), $reset = FALSE) {
  if (!$uid) {
    global $user;
    $uid = $user->uid;
  }

  $controller = entity_get_controller('comstack_conversation');
  if ($reset) {
    $controller->resetCache();
  }

  // Add the user id condition.
  $conditions['uid'] = $uid;

  return $controller->load($conversation_ids, $conditions);
}

/**
 * Access callback for the cs_pm bundle of the message entity.
 */
function _comstack_message_access__cs_pm($op, $entity, $account, $entity_type) {
  return TRUE;
}
