<?php

/**
 * @file
 * Contains ComstackPMCurrentUserResource__1_0.
 */

class ComstackPMCurrentUserResource__1_0 extends \ComstackUsersResource__1_0 {
  /**
   * Overrides \RestfulDataProviderEFQ::controllersInfo().
   */
  public static function controllersInfo() {
    return array(
      '' => array(
        \RestfulInterface::GET => 'getUser',
      ),
    );
  }

  /**
   * Overrides \RestfulBase::isListRequest().
   *
   * This endpoint will never return a list, ever!
   */
  public function isListRequest() {
    return FALSE;
  }

  /**
   * Return information on the current user.
   */
  public function getUser() {
    $account = $this->getAccount();

    $forced_read_only = (bool) variable_get('comstack_pm_killswitch__enabled', FALSE);
    $user_pm_enabled = $forced_read_only ? FALSE : user_preferences($account->uid, 'comstack_pm_enabled');
    $friends_read_only = variable_get('comstack_friends_killswitch__enabled', FALSE);

    $data = array(
      'user' => $this->viewEntity($account->uid),
      'permissions' => array(
        'conversations' => array(
          'start' => $user_pm_enabled && user_access('start new comstack conversations', $account),
          'leave' => $user_pm_enabled && user_access('delete leave comstack conversation', $account),
          'reply' => $user_pm_enabled && user_access('reply to a comstack conversation', $account),
          'invite_others' => $user_pm_enabled && user_access('invite users to a comstack conversation', $account),
          'set_title' => $user_pm_enabled && user_access('set a comstack conversations title', $account),
          'mark_as_read' => user_access('mark a comstack conversation as read', $account),
          'mute' => user_access('mute a comstack conversation', $account),
          'archive' => user_access('archive a comstack conversation', $account),
          'pin' => user_access('pin a comstack conversation', $account),
          'star' => user_access('star a comstack conversation', $account),
          'report' => $user_pm_enabled && user_access('create comstack_pm_report entries', $account),
        ),
        'messages' => array(
          'edit_own' => $user_pm_enabled && user_access('edit own comstack conversation messages', $account),
          'delete' => $user_pm_enabled && user_access('delete own comstack conversation messages', $account),
        ),
        'users' => array(
          'friend' => !$friends_read_only && user_access('can request comstack_friends relationships', $account),
          'block' => !$friends_read_only && user_access('can request comstack_blocked relationships', $account),
        ),
      ),
      'preferences' => array(
        'read_only_mode' => !$user_pm_enabled,
        'forced_read_only' => $forced_read_only,
      ),
    );

    return $data;
  }
}
