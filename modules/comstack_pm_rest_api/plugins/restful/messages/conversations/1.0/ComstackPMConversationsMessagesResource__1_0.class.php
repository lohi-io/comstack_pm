<?php

/**
 * @file
 * Contains ComstackPMConversationsMessagesResource__1_0.
 */

class ComstackPMConversationsMessagesResource__1_0 extends \ComstackPMMessagesResource__1_0 {
  protected $entity_id;

  /**
   * Overrides \RestfulEntityBase::controllersInfo().
   */
  public static function controllersInfo() {
    return array(
      '' => array(
        \RestfulInterface::GET => 'viewConversationMessages',
        \RestfulInterface::POST => 'deleteConversationMessages',
      ),
    );
  }

  /**
   * Return the entity ID found from the request URL.
   */
  protected function getEntityID() {
    // If we've not set the entity id, do it.
    if (!$this->entity_id) {
      $entity_id = NULL;

      // Still?? Something isn't right here, throw an exception.
      if (!$entity_id) {
        throw new RestfulBadRequestException('Path does not exist');
      }
    }

    return $entity_id;
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

    if () {

    }

    return comstack_pm_message_access($op, $entity, $account);
  }

  /**
   * GET a list of messages within this conversation, this is a copy of:
   * \RestfulEntityBase::getList() but with a filter hard coded to return
   * a list of messages via entity reference.
   *
   * The viewEntities() method is provided by \ComstackPMMessagesResource__1_0
   * which makes our lives easier, only need to worry about implementing our
   * own version of getList().
   */
  public function viewConversationMessages() {
    $request = $this->getRequest();
    print_r($request);exit;
  }

  /**
   * DELETE messages from a conversation.
   */
  public function deleteConversationMessages() {
    /*$entity_info = $this->getEntityInfo();
    $bundle_key = $entity_info['entity keys']['bundle'];
    $values = $bundle_key ? array($bundle_key => $this->bundle) : array();

    $entity = entity_create($this->entityType, $values);

    if ($this->checkEntityAccess('create', $this->entityType, $entity) === FALSE) {
      // User does not have access to create entity.
      $params = array('@resource' => $this->getPluginKey('label'));
      throw new RestfulForbiddenException(format_string('You do not have access to create a new @resource resource.', $params));
    }

    $wrapper = entity_metadata_wrapper($this->entityType, $entity);

    $this->setPropertyValues($wrapper);
    return array($this->viewEntity($wrapper->getIdentifier()));*/
  }
}
