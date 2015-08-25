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

