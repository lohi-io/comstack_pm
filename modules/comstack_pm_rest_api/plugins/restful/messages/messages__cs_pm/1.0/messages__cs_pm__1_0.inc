<?php

// For a full commented resource plugin definition see...
// https://github.com/lohi-io/comstack_pm/blob/7.x-1.x/modules/comstack_pm_rest_api/plugins/restful/users/available_users/1.0/available_users__1_0.inc

$plugin = array(
  'label' => t('Messages'),
  'resource' => 'cs-pm/messages',
  'name' => 'messages__cs_pm__1_0',
  'description' => t('Get a specific message or update a messages text.'),
  'class' => 'ComstackPMMessagesResource__1_0',
  'entity_type' => 'message',
  'bundle' => 'cs_pm',
  'authentication_types' => TRUE,
  'authentication_optional' => FALSE,
  'autocomplete' => array(
    'enable' => FALSE,
  ),
);
