<?php

/**
 * @file
 * Contains ComstackPMConversationsMessagesResource__1_0.
 */

class ComstackPMConversationsMessagesResource__1_0 extends \ComstackPMMessagesResource__1_0 {
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

  /**
   * Return the entity ID found from the request URL.
   */
  protected function getEntityID() {
    // If we've not set the entity id, do it.
    if (!$this->wildcard_entity_id) {
      $entity_id = NULL;

      $request = $this->getRequest();

      // Take the request, find the last numeric chunk.
      if (isset($request['q'])) {
        $url_parts = explode('/', $request['q']);
        foreach (array_reverse($url_parts) as $part) {
          if (ctype_digit((string) $part) && $part > 0) {
            $this->wildcard_entity_id = $part;
            break;
          }
        }
      }

      // Still?? Something isn't right here, throw an exception.
      if (!$this->wildcard_entity_id) {
        throw new RestfulBadRequestException('Path does not exist');
      }
    }

    return $this->wildcard_entity_id;
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
   * Overrides \RestfulEntityBase::getList().
   */
  public function getList() {
    if (!$this->checkConversationAccess()) {
      return array();
    }

    return parent::getList();
  }

  /**
   * DELETE messages from a conversation.
   */
  public function deleteConversationMessages() {
    $conversation_id = $this->getEntityID();


  }
}
