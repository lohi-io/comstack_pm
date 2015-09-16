<?php

// For a full commented resource plugin definition see...
// https://github.com/lohi-io/comstack_pm/blob/7.x-1.x/modules/comstack_pm_rest_api/plugins/restful/users/available_users/1.0/available_users__1_0.inc

$plugin = array(
  'label' => t('Conversations'),
  'resource' => 'cs-pm/conversations',
  'name' => 'conversations__1_0',
  'description' => t('Interact with Conversations with the usual CRUD operations along with other actions.'),
  'class' => 'ComstackPMConversationsResource__1_0',
  'entity_type' => 'comstack_conversation',
  'bundle' => 'comstack_conversation',
  'authentication_types' => TRUE,
  'authentication_optional' => FALSE,
  'autocomplete' => array(
    'enable' => FALSE,
  ),
  'formatter' => 'comstack_json',
);
