<?php
/**
 * @file
 * comstack_pm_default_permissions.features.user_permission.inc
 */

/**
 * Implements hook_user_default_permissions().
 */
function comstack_pm_default_permissions_user_default_permissions() {
  $permissions = array();

  // Exported permission: 'archive a comstack conversation'.
  $permissions['archive a comstack conversation'] = array(
    'name' => 'archive a comstack conversation',
    'roles' => array(
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'comstack_pm',
  );

  // Exported permission: 'bypass comstack_pm access checks'.
  $permissions['bypass comstack_pm access checks'] = array(
    'name' => 'bypass comstack_pm access checks',
    'roles' => array(),
    'module' => 'comstack_pm',
  );

  // Exported permission: 'delete leave comstack conversation'.
  $permissions['delete leave comstack conversation'] = array(
    'name' => 'delete leave comstack conversation',
    'roles' => array(
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'comstack_pm',
  );

  // Exported permission: 'delete own comstack conversation messages'.
  $permissions['delete own comstack conversation messages'] = array(
    'name' => 'delete own comstack conversation messages',
    'roles' => array(
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'comstack_pm',
  );

  // Exported permission: 'edit own comstack conversation messages'.
  $permissions['edit own comstack conversation messages'] = array(
    'name' => 'edit own comstack conversation messages',
    'roles' => array(
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'comstack_pm',
  );

  // Exported permission: 'invite users to a comstack conversation'.
  $permissions['invite users to a comstack conversation'] = array(
    'name' => 'invite users to a comstack conversation',
    'roles' => array(
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'comstack_pm',
  );

  // Exported permission: 'mark a comstack conversation as read'.
  $permissions['mark a comstack conversation as read'] = array(
    'name' => 'mark a comstack conversation as read',
    'roles' => array(
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'comstack_pm',
  );

  // Exported permission: 'mute a comstack conversation'.
  $permissions['mute a comstack conversation'] = array(
    'name' => 'mute a comstack conversation',
    'roles' => array(
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'comstack_pm',
  );

  // Exported permission: 'pin a comstack conversation'.
  $permissions['pin a comstack conversation'] = array(
    'name' => 'pin a comstack conversation',
    'roles' => array(
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'comstack_pm',
  );

  // Exported permission: 'reply to a comstack conversation'.
  $permissions['reply to a comstack conversation'] = array(
    'name' => 'reply to a comstack conversation',
    'roles' => array(
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'comstack_pm',
  );

  // Exported permission: 'set a comstack conversations title'.
  $permissions['set a comstack conversations title'] = array(
    'name' => 'set a comstack conversations title',
    'roles' => array(
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'comstack_pm',
  );

  // Exported permission: 'star a comstack conversation'.
  $permissions['star a comstack conversation'] = array(
    'name' => 'star a comstack conversation',
    'roles' => array(
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'comstack_pm',
  );

  // Exported permission: 'start new comstack conversations'.
  $permissions['start new comstack conversations'] = array(
    'name' => 'start new comstack conversations',
    'roles' => array(
      'authenticated user' => 'authenticated user',
    ),
    'module' => 'comstack_pm',
  );

  // Exported permission: 'view deleted comstack conversations'.
  $permissions['view deleted comstack conversations'] = array(
    'name' => 'view deleted comstack conversations',
    'roles' => array(),
    'module' => 'comstack_pm',
  );

  return $permissions;
}
