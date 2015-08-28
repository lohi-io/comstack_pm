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
    'controller class' => 'EntityAPIController',
    'entity class' => 'ComstackConversation',
    'base table' => 'comstack_conversation',
    'fieldable' => TRUE,
    'entity keys' => array(
      'id' => 'conversation_id',
      'label' => 'title',
    ),
    'module' => 'comstack_pm',
    'entity cache' => module_exists('entitycache'),
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

  $conversation_properties['started'] = array(
    'label' => t('Date started'),
    'type' => 'date',
    'description' => t('The date the conversation was created.'),
    'setter callback' => 'entity_property_verbatim_set',
    'setter permission' => 'administer comstack content',
    'schema field' => 'started',
  );
  $conversation_properties['updated'] = array(
    'label' => t('Date last updated'),
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
  $conversation_properties['messages_count'] = array(
    'label' => t('Messages count'),
    'description' => t('Number of messages in the conversation.'),
    'type' => 'integer',
    'setter callback' => 'entity_property_verbatim_set',
    'schema field' => 'messages_count',
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
      if ($user_wrapper->uid->value() == $account->uid) {
        // Found!
        $is_a_participant = TRUE;
      }
    }
  }

  // Check the other operations.
  if ($is_a_participant) {
    if ($op === 'view') {
      return TRUE;
    }
    elseif ($op === 'update' && user_access('set a comstack conversations title', $account)) {
      return TRUE;
    }
    elseif ($op === 'delete' && user_access('delete a comstack conversation', $account)) {
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
      $wrapper = entity_metadata_wrapper($entity_type, $entity);
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
