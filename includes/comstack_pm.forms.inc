<?php
/**
 * @file
 * comstack_pm.forms.inc
 */

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * Adds extra setting fields to the type form.
 */
function comstack_pm_form_comstack_message_type_form_alter(&$form, &$form_state, $form_id) {
  $comstack_message_type = $form['comstack_message_type']['#value'];

  if (!isset($comstack_message_type->type) || isset($comstack_message_type->type) &&  $comstack_message_type->type !== 'cs_pm') {
    return;
  }

  $form['pm'] = array(
    '#type' => 'fieldset',
    '#title' => t('Private Messaging'),
    '#collapsible' => TRUE,
    '#group' => 'tabs',
  );

  $form['pm']['conversation_record_separate_transcripts'] = array(
    '#type' => 'checkbox',
    '#title' => t('Each participant in a conversation should have their own version of the dialogue history'),
    '#default_value' => isset($comstack_message_type->conversation_record_separate_transcripts) ? $comstack_message_type->conversation_record_separate_transcripts : 0,
    '#description' => t("This will affect how Comstack records conversation histories, unchecked there will be one transcript for all. The reason for this option is around deletion of messages - if User A sends a message to User B then deletes it, should User B still see that message? If yes check the box!"),
  );

  $form['general']['subject']['#description'] .= ' ' . t('For types with conversation structure, the conversation can be named instead of individual messages.');
}

/**
 * Admin settings form callback.
 */
function comstack_pm_admin_settings_form() {
  $form = array();

  $form['killswitch'] = array(
    '#type' => 'fieldset',
    '#title' => t('Forced read only mode'),
    '#collapsible' => TRUE,
  );

  $form['killswitch']['guidance_text'] = array(
    '#markup' => '<p class="help-text">' . t('Forced read only mode is intended as a temporary measure and allows you to prevent new conversations or private messages being created.') . '</p>',
  );

  $form['killswitch']['comstack_pm_killswitch__enabled'] = array(
    '#type' => 'checkbox',
    '#title' => t('Force read only mode on'),
    '#default_value' => variable_get('comstack_pm_killswitch__enabled', FALSE),
    '#description' => t("Check the box if you want to prevent users from sending new messages and starting new conversations."),
  );

  $form['killswitch']['comstack_pm_killswitch__text'] = array(
    '#type' => 'textarea',
    '#title' => t('Warning message text'),
    '#description' => t('The text to display to the user about forced read only mode being active. This is the only text displayed to the user about this.'),
    '#default_value' => filter_xss(variable_get('comstack_pm_killswitch__text', 'Private messaging has been temporarily disabled.')),
    '#states' => array(
      'visible' => array(
        ':input[id="edit-comstack-pm-killswitch-enabled"]' => array('checked' => TRUE),
      ),
    ),
  );

  return system_settings_form($form);
}
