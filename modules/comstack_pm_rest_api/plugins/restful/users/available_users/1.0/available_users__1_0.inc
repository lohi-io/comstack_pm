<?php

$plugin = array(
  'label' => t('Available Users'),
  // All of the comstack private messaging rest api endpoints are prefixed.
  'resource' => 'cs-pm/users/available-users',
  // The name index must match this files name.
  'name' => 'available_users__1_0',
  'description' => t('Get a list of users that the current user can contact via Private Messaging.'),
  'class' => 'ComstackPMAvailableUsersResource__1_0',
  // We're inheriting from the base user restful data provider, but still add
  // the entity type and bundle keys. If not working with an entity type then
  // you should set 'entity_type' to FALSE.
  'entity_type' => 'user',
  'bundle' => 'user',
  // Require authentication by any method.
  'authentication_types' => TRUE,
  'authentication_optional' => FALSE,
  // Rate limit access to this endpoint.
  /*'rate_limit' => array(
    // The 'request' event is the basic event.
    'request' => array(
      'event' => 'request',
      // Rate limit is cleared every day.
      // http://php.net/manual/en/dateinterval.construct.php
      'period' => new \DateInterval('P1D'),
      'limits' => array(
        //'anonymous user' => 2,
        'authenticated user' => 3,
        'administrator' => \RestfulRateLimitManager::UNLIMITED_RATE_LIMIT,
      ),
    ),
  ),*/
  // Allow autocomplete on this endpoint.
  'autocomplete' => array(
    'enable' => TRUE,
    // You can define a default operator for the autocomplete, can be the
    // strings (not constants) STARTS_WITH, CONTAINS.
    // https://github.com/RESTful-Drupal/restful/blob/7.x-1.x/docs/api_drupal.md#autocomplete
    'operator' => 'STARTS_WITH',
  ),
);
