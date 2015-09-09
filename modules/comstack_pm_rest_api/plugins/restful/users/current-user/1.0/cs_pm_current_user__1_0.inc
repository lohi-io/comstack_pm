<?php

$plugin = array(
  'label' => t('Current User'),
  'resource' => 'cs-pm/users/current-user',
  'name' => 'cs_pm_current_user__1_0',
  'description' => t('Get the current users information and their permissions.'),
  'class' => 'ComstackPMCurrentUserResource__1_0',
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
