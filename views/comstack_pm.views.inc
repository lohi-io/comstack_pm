<?php

/**
 * @file
 * Provide additional Views handlers.
 */

/**
 * Implements hook_views_data_alter().
 */
function comstack_pm_views_data_alter(&$data) {
  $data['comstack_conversation']['participants'] = array(
    'title' => t('Conversation participants'),
    'help' => t('List of active participants in a conversation, linked to each users profile.'),
    'field' => array(
      'handler' => 'comstack_pm_handler_field_entity_ref_labels',
      'target_field' => 'cs_pm_participants',
      'link_permissions' => array('access user profiles', 'administer users'),
    ),
  );
  $data['comstack_conversation']['historical_participants'] = array(
    'title' => t('Conversation historical participants'),
    'help' => t('List of historical participants in a conversation, linked to each users profile.'),
    'field' => array(
      'handler' => 'comstack_pm_handler_field_entity_ref_labels',
      'target_field' => 'cs_pm_historical_participants',
      'link_permissions' => array('access user profiles', 'administer users'),
    ),
  );
}
