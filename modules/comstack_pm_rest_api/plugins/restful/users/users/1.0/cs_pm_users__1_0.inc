<?php

$plugin = array(
  'label' => t('Users'),
  'resource' => 'cs-pm/users',
  'name' => 'cs_pm_users__1_0',
  'description' => t('Get a specific user formatted as per Comstack REST API documentation.'),
  'class' => 'ComstackPMUsersResource__1_0',
  'major_version' => 1,
  'minor_version' => 0,
  'entity_type' => 'user',
  'bundle' => 'user',
  'authentication_types' => TRUE,
  'authentication_optional' => FALSE,
  'autocomplete' => array(
    'enable' => FALSE,
  ),
);
