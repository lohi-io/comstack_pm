<?php

/**
 * @file
 * Contains ComstackPMConversationsResource__1_0.
 */

class ComstackPMConversationsResource__1_0 extends \ComstackRestfulEntityBase {
  // Set the default range for listings.
  protected $range = 25;

  /**
   * Overrides \RestfulEntityBase::controllersInfo().
   */
  public static function controllersInfo() {
    return array(
      // Listings.
      '' => array(
        // GET returns a list of entities.
        \RestfulInterface::GET => 'getList',
        // POST
        \RestfulInterface::POST => 'createEntity',
      ),
      // A specific entity.
      '^(\d+)*\d+$' => array(
        \RestfulInterface::GET => 'viewEntities',
        \RestfulInterface::DELETE => 'leave',
      ),
      // Actions against a specific conversation.
      '^([\d]+)\/reply' => array(
        \RestfulInterface::POST => 'reply',
      ),
      '^([\d]+)\/mark-as-read' => array(
        \RestfulInterface::PUT => 'markAsRead',
        \RestfulInterface::DELETE => 'markAsUnread',
      ),
      '^([\d]+)\/leave' => array(
        \RestfulInterface::PUT => 'leave',
      ),
      '^([\d]+)\/invite' => array(
        \RestfulInterface::POST => 'invite',
      ),
      '^([\d]+)\/title' => array(
        \RestfulInterface::POST => 'setTitle',
      ),
      '^([\d]+)\/mute' => array(
        \RestfulInterface::PUT => 'mute',
        \RestfulInterface::DELETE => 'unMute',
      ),
      '^([\d]+)\/archive' => array(
        \RestfulInterface::PUT => 'archive',
        \RestfulInterface::DELETE => 'unArchive',
      ),
      '^([\d]+)\/pin' => array(
        \RestfulInterface::PUT => 'pin',
        \RestfulInterface::DELETE => 'unPin',
      ),
      '^([\d]+)\/star' => array(
        \RestfulInterface::PUT => 'star',
        \RestfulInterface::DELETE => 'unStar',
      ),
      '^([\d]+)\/report' => array(
        \RestfulInterface::POST => 'report',
      ),
      '^([\d]+)\/search' => array(
        \RestfulInterface::POST => 'search',
      ),
    );
  }

  /**
   * Overrides \RestfulEntityBase::getQueryForList().
   *
   * Only expose conversations which haven't been deleted.
   */
  public function getQueryForList() {
    $query = parent::getQueryForList();
    if (!user_access('view deleted comstack conversations', $this->getAccount())) {
      $query->propertyCondition('deleted', 0);
    }
    return $query;
  }

  /**
   * Overrides \RestfulDataProviderEFQ::getQueryCount().
   */
  public function getQueryCount() {
    $query = parent::getQueryCount();
    if (!user_access('view deleted comstack conversations', $this->getAccount())) {
      $query->propertyCondition('deleted', 0);
    }
    return $query->count();
  }

  /**
   * Overrides \RestfulEntityBase::publicFieldsInfo().
   */
  public function publicFieldsInfo() {
    $public_fields = parent::publicFieldsInfo();

    // Reorder things.
    $id_field = $public_fields['id'];
    unset($public_fields['id']);

    $public_fields['type'] = array(
      'callback' => array('\RestfulManager::echoMessage', array('conversation')),
    );

    $public_fields['id'] = $id_field;

    $public_fields['participants'] = array(
      'property' => 'cs_pm_participants',
      // For an explanation of this check out:
      // \ComstackPMMessagesResource__1_0::publicFieldsInfo().
      'resource' => array(
        'user' => array(
          'name' => 'cs-pm/users',
          'full_view' => TRUE,
        ),
      ),
    );

    $public_fields['historical_participants'] = array(
      'property' => 'cs_pm_historical_participants',
      'resource' => array(
        'user' => array(
          'name' => 'cs-pm/users',
          'full_view' => TRUE,
        ),
      ),
    );

    $public_fields['started_by'] = array(
      'property' => 'cs_pm_started_by',
      'resource' => array(
        'user' => array(
          'name' => 'cs-pm/users',
          'full_view' => TRUE,
        ),
      ),
    );

    $public_fields['last_updated_by'] = array(
      'property' => 'cs_pm_last_updated_by',
      'resource' => array(
        'user' => array(
          'name' => 'cs-pm/users',
          'full_view' => TRUE,
        ),
      ),
    );

    $public_fields['started'] = array(
      'property' => 'started',
      'process_callbacks' => array(
        'date_iso8601',
      ),
    );

    $public_fields['updated'] = array(
      'property' => 'updated',
      'process_callbacks' => array(
        'date_iso8601',
      ),
    );

    $public_fields['container'] = array(
      'property' => 'container',
    );

    $public_fields['title'] = array(
      'property' => 'title',
    );

    $public_fields['unread_count'] = array(
      'property' => 'unread_count',
    );

    $public_fields['pinned'] = array(
      'property' => 'pinned',
    );

    $public_fields['archived'] = array(
      'property' => 'archived',
    );

    $public_fields['muted'] = array(
      'property' => 'muted',
    );

    $public_fields['forwarded'] = array(
      'property' => 'forwarded',
    );

    $public_fields['deleted'] = array(
      'property' => 'deleted',
    );

    unset($public_fields['label']);
    unset($public_fields['self']);

    return $public_fields;
  }

  /**
   * Overrides \RestfulEntityBase::propertyValuesPreprocessText().
   *
   * The input format is hardcoded into this function, use a variable instead.
   */
  protected function propertyValuesPreprocessText($property_name, $value, $field_info) {
    // Text field. Check if field has an input format.
    $instance = field_info_instance($this->getEntityType(), $property_name, $this->getBundle());
    $format = variable_get('comstack_pm_input_format', 'cs_pm');

    if ($field_info['cardinality'] == 1) {
      // Single value.
      if (!$instance['settings']['text_processing']) {
        return $value;
      }

      return array (
        'value' => $value,
        'format' => $format,
      );
    }

    // Multiple values.
    foreach ($value as $delta => $single_value) {
      if (!$instance['settings']['text_processing']) {
        $return[$delta] = $single_value;
      }
      else {
        $return[$delta] = array(
          'value' => $single_value,
          'format' => $format,
        );
      }
    }
    return $return;
  }

  /**
   * Overrides \RestfulEntityBase::createEntity().
   *
   * @return object
   *
   * @throws \RestfulBadRequestException
   */
  public function createEntity() {
    // Check that this user has permission to create new conversations.
    if ($this->checkEntityAccess('create', $this->entityType, $entity) === FALSE) {
      // User does not have access to create entity.
      $params = array('@resource' => $this->getPluginKey('label'));
      throw new RestfulForbiddenException(format_string('You do not have access to create a new @resource resource.', $params));
    }

    $account = $this->getAccount();
    $request_data = $this->getRequestData();

    // Validate the request has all the data we need.
    if (empty($request_data['recipients']) || empty($request_data['text']) || isset($request_data['recipients']) && !is_array($request_data['recipients']) || isset($request_data['text']) && !is_string($request_data['text'])) {
      throw new \RestfulBadRequestException("The data you're attempting to create a conversation with is either incomplete or has invalid values.");
    }

    // Add the current user to the list of recipients/participants.
    if (!in_array($account->uid, $request_data['recipients'])) {
      $request_data['recipients'][] = $account->uid;
    }

    $data = array(
      'participants' => $request_data['recipients'],
      'text' => $request_data['text'],
      'uid' => $account->uid,
    );

    $conversation = comstack_pm_new_conversation($data);

    return array($this->viewEntity($conversation->conversation_id));
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
   * Check that the current user can update this conversation.
   */
  public function checkUpdateAccess($conversation) {
    if ($this->checkEntityAccess('update', $this->entityType, $conversation) === FALSE) {
      throw new RestfulForbiddenException("You don't have access to update this conversation.");
    }

    return TRUE;
  }

  /**
   * Reply to a conversation.
   *
   * @return object
   *
   * @throws \RestfulBadRequestException
   */
  public function reply() {
    $account = $this->getAccount();
    $conversation = $this->getConversation();

    // Check access.
    if (!user_access('bypass comstack_pm access checks', $account)) {
      if (!user_access('reply to a comstack conversation', $account) || $this->checkEntityAccess('view', $this->entityType, $conversation) === FALSE) {
        throw new RestfulForbiddenException("You don't have access to reply to this conversation.");
      }
    }

    // Check that there's text.
    $request_data = $this->getRequestData();

    // Validate the request has all the data we need.
    if (empty($request_data['text']) || isset($request_data['text']) && !is_string($request_data['text'])) {
      throw new \RestfulBadRequestException("The text you're trying to create a reply with isn't valid, it empty?");
    }

    $message = $conversation->reply($request_data['text']);

    // Load the message handler and render the message.
    $handler = restful_get_restful_handler('cs-pm/messages');
    // Set the same account on the handler.
    $handler->setAccount($account);

    return array($handler->viewEntity($message->mid));
  }

  /**
   * Mark a conversation as read.
   */
  public function markAsRead() {
    $account = $this->getAccount();
    if (!user_access('mark a comstack conversation as read', $account)) {
      throw new RestfulForbiddenException("You can't mark conversations as read.");
    }

    $conversation = $this->getConversation();
    $this->checkUpdateAccess($conversation);
    $conversation->markAsRead();
  }

  /**
   * Mark a conversation as unread.
   */
  public function markAsUnread() {
    $account = $this->getAccount();
    if (!user_access('mark a comstack conversation as read', $account)) {
      throw new RestfulForbiddenException("You can't mark conversations as read.");
    }

    $conversation = $this->getConversation();
    $this->checkUpdateAccess($conversation);
    $conversation->markAsUnread();
  }

  /**
   * Leave the conversation.
   */
  public function leave() {
    $conversation = $this->getConversation();

    if ($this->checkEntityAccess('delete', $this->entityType, $conversation) === FALSE) {
      throw new RestfulForbiddenException("You don't have access to leave to this conversation.");
    }

    $conversation->leave();
  }

  /**
   * Mark a conversation as read.
   *
   * @throws RestfulBadRequestException
   */
  public function invite() {
    $account = $this->getAccount();
    if (!user_access('invite users to a comstack conversation', $account)) {
      throw new RestfulForbiddenException("You don't have access to invite users to this conversation.");
    }

    $conversation = $this->getConversation();
    $request_data = $this->getRequestData();

    // Check access against this entity.
    if ($this->checkEntityAccess('view', $this->entityType, $conversation) === FALSE) {
      throw new RestfulForbiddenException("You don't have access to this conversation.");
    }

    // Validate the ids.
    if (empty($request_data['ids']) || !empty($request_data['ids']) && !is_array($request_data['ids'])) {
      throw new \RestfulBadRequestException('In order to invite people to this conversation you need to provide an array of user IDs.');
    }

    $conversation->invite($request_data['ids']);
  }

  /**
   * Set the title of a conversation.
   *
   * @throws RestfulBadRequestException
   */
  public function setTitle() {
    $account = $this->getAccount();
    if (!user_access('set a comstack conversations title', $account)) {
      throw new RestfulForbiddenException("You can't set conversation titles.");
    }

    $conversation = $this->getConversation();
    $this->checkUpdateAccess($conversation);
    $request_data = $this->getRequestData();

    // Validate the ids.
    if (empty($request_data['text']) || isset($request_data['text']) && !is_string($request_data['text'])) {
      throw new \RestfulBadRequestException('You need to pass in a string, even an empty one to set this conversations title.');
    }

    $conversation->setTitle($request_data['text']);
  }

  /**
   * Mute this conversation.
   */
  public function mute() {
    $account = $this->getAccount();
    if (!user_access('mute a comstack conversation', $account)) {
      throw new RestfulForbiddenException("You can't mute or unmute conversations.");
    }

    $conversation = $this->getConversation();
    $this->checkUpdateAccess($conversation);
    $conversation->mute();
  }

  /**
   * UnMute this conversation.
   */
  public function unMute() {
    $account = $this->getAccount();
    if (!user_access('mute a comstack conversation', $account)) {
      throw new RestfulForbiddenException("You can't mute or unmute conversations.");
    }

    $conversation = $this->getConversation();
    $this->checkUpdateAccess($conversation);
    $conversation->unMute();
  }

  /**
   * Archive this conversation.
   */
  public function archive() {
    $account = $this->getAccount();
    if (!user_access('archive a comstack conversation', $account)) {
      throw new RestfulForbiddenException("You can't archive or unarchive conversations.");
    }

    $conversation = $this->getConversation();
    $this->checkUpdateAccess($conversation);
    $conversation->archive();
  }

  /**
   * UnArchive this conversation.
   */
  public function unArchive() {
    $account = $this->getAccount();
    if (!user_access('archive a comstack conversation', $account)) {
      throw new RestfulForbiddenException("You can't archive or unarchive conversations.");
    }

    $conversation = $this->getConversation();
    $this->checkUpdateAccess($conversation);
    $conversation->unArchive();
  }

  /**
   * Pin this conversation.
   */
  public function pin() {
    $account = $this->getAccount();
    if (!user_access('pin a comstack conversation', $account)) {
      throw new RestfulForbiddenException("You can't pin or unpin conversations.");
    }

    $conversation = $this->getConversation();
    $this->checkUpdateAccess($conversation);
    $conversation->pin();
  }

  /**
   * UnPin this conversation.
   */
  public function unPin() {
    $account = $this->getAccount();
    if (!user_access('pin a comstack conversation', $account)) {
      throw new RestfulForbiddenException("You can't pin or unpin conversations.");
    }

    $conversation = $this->getConversation();
    $this->checkUpdateAccess($conversation);
    $conversation->unPin();
  }

  /**
   * Star this conversation.
   */
  public function star() {
    $account = $this->getAccount();
    if (!user_access('star a comstack conversation', $account)) {
      throw new RestfulForbiddenException("You can't star or unstar conversations.");
    }

    $conversation = $this->getConversation();
    $this->checkUpdateAccess($conversation);
    $conversation->star();
  }

  /**
   * UnStar this conversation.
   */
  public function unStar() {
    $account = $this->getAccount();
    if (!user_access('star a comstack conversation', $account)) {
      throw new RestfulForbiddenException("You can't star or unstar conversations.");
    }

    $conversation = $this->getConversation();
    $this->checkUpdateAccess($conversation);
    $conversation->unStar();
  }
}
