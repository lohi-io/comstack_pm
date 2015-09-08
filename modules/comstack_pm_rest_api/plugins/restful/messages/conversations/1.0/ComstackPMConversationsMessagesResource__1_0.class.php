<?php

/**
 * @file
 * Contains ComstackPMConversationsMessagesResource__1_0.
 */

class ComstackPMConversationsMessagesResource__1_0 extends \ComstackPMMessagesResource__1_0 {
  protected $range = 10;
  protected $cursor_paging = TRUE;

  /**
   * Overrides \RestfulEntityBase::controllersInfo().
   */
  public static function controllersInfo() {
    return array(
      '' => array(
        \RestfulInterface::GET => 'getList',
      ),
      'delete' => array(
        \RestfulInterface::POST => 'deleteConversationMessages',
      ),
    );
  }

  protected function checkConversationAccess() {
    $account = $this->getAccount();

    $conversation_id = $this->getEntityID();
    $conversation = entity_load_single('comstack_conversation', $conversation_id);

    if ($conversation && comstack_pm_conversation_access('view', $conversation, $account)) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Overrides \RestfulEntityBase::checkEntityAccess().
   *
   * Check we've got access to the conversation, if deleting then just check
   * that we can perform the action, the method callback will check that the
   * current user is the author of the message.
   */
  protected function checkEntityAccess($op, $entity_type, $entity) {
    $account = $this->getAccount();

    // Validate that the user has access to the conversation.
    if ($op === 'view' && comstack_pm_message_access($op, $entity, $account)) {
      return TRUE;
    }
    elseif ($op === 'delete' && user_access('delete own comstack conversation messages', $account)) {
      // Check that the user has access to this conversation.
      if ($this->checkConversationAccess()) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Overrides \RestfulEntityBase::getQueryForList().
   *
   * Only expose conversations which haven't been deleted.
   */
  public function getQueryForList() {
    $conversation_id = $this->getEntityID();
    $query = parent::getQueryForList();

    $query->fieldCondition('cs_pm_conversation', 'target_id', $conversation_id);

    // We loop in transcripts via hook_query_tag alter.
    $query->addTag('comstack_pm_separate_transcripts');

    return $query;
  }

  /**
   * Overrides \RestfulDataProviderEFQ::getQueryCount().
   */
  public function getQueryCount() {
    $conversation_id = $this->getEntityID();
    $query = parent::getQueryCount();

    $query->fieldCondition('cs_pm_conversation', 'target_id', $conversation_id);

    // We loop in transcripts via hook_query_tag alter.
    $query->addTag('comstack_pm_separate_transcripts');

    return $query->count();
  }

  /**
   * Load the current conversation being accessed.
   *
   * @throws \RestfulGoneException
   */
  public function getConversation() {
    $conversation_id = $this->getEntityID();
    $account = $this->getAccount();
    $conversation = comstack_conversation_load($conversation_id, $account->uid);

    if (!$conversation) {
      throw new RestfulGoneException(t("We're having trouble loading that conversation, might want to tell someone about that."));
    }

    return $conversation;
  }

  /**
   * Overrides \RestfulEntityBase::getList().
   */
  public function getList() {
    if (!$this->checkConversationAccess()) {
      throw new RestfulForbiddenException("You don't have access to this conversation.");
    }

    return parent::getList();
  }

  /**
   * DELETE messages from a conversation.
   */
  public function deleteConversationMessages() {
    $conversation = $this->getConversation();
    $request_data = $this->getRequestData();

    if ($this->checkEntityAccess('delete', $this->entityType, $conversation) === FALSE) {
      throw new RestfulForbiddenException("You don't have access to this conversation.");
    }

    // Validate the ids.
    if (empty($request_data['ids']) || !empty($request_data['ids']) && !is_array($request_data['ids'])) {
      throw new \RestfulBadRequestException("In order to delete messages from this conversation, you'll need to send some valid ids.");
    }

    $conversation->deleteMessages($request_data['ids']);
  }
}
