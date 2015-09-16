<?php

// For a full commented resource plugin definition see...
// https://github.com/lohi-io/comstack_pm/blob/7.x-1.x/modules/comstack_pm_rest_api/plugins/restful/users/available_users/1.0/available_users__1_0.inc

$plugin = array(
  'label' => t('Conversation Messages'),
  'resource' => 'cs-pm/conversations/%/messages',
  'name' => 'messages__conversations__1_0',
  'description' => t('Get messages that belong to a conversation.'),
  'class' => 'ComstackPMConversationsMessagesResource__1_0',
  'entity_type' => 'message',
  'bundle' => 'cs_pm',
  'authentication_types' => TRUE,
  'authentication_optional' => FALSE,
  'autocomplete' => array(
    'enable' => FALSE,
  ),
  // This endpoint will live off of cs-pm/conversations, to do this we need to
  // define our own hook_menu implementation.
  'hook_menu' => FALSE,
  'formatter' => 'comstack_json',
);
