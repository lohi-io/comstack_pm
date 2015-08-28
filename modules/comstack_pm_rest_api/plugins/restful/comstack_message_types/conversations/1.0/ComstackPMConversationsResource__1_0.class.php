<?php

/**
 * @file
 * Contains ComstackPMConversationsResource__1_0.
 */

class ComstackPMConversationsResource__1_0 extends \RestfulEntityBase {
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
        \RestfulInterface::DELETE => 'deleteEntity',
      ),
      // Actions against a specific conversation.
      '^([\d]+)\/reply' => array(
        \RestfulInterface::POST => 'postReply',
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
   * Return the entity ID found from the request URL.
   */
  protected function getEntityID() {
    // If we've not set the entity id, do it.
    if (!$this->entity_id) {
      $entity_id = NULL;

      $request = $this->getRequest();

      // Take the request, find the last numeric chunk.
      if (isset($request['q'])) {
        $url_parts = explode('/', $request['q']);
        foreach (array_reverse($url_parts) as $part) {
          if (is_numeric($part) && $part > 0) {
            $this->entity_id = $part;
            break;
          }
        }
      }

      // Still?? Something isn't right here, throw an exception.
      if (!$this->entity_id) {
        throw new RestfulBadRequestException('Path does not exist');
      }
    }

    return $this->entity_id;
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

    // Shuffle things around.
    /*$title = $public_fields['title'];
    unset($public_fields['title']);
    $public_fields['title'] = $title;*/

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
    $format = variable_get('comstack_pm_rest_input_format', 'cs_pm');

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

  function quack($entity_id) {
    return array('quack' => $entity_id);
  }
}
