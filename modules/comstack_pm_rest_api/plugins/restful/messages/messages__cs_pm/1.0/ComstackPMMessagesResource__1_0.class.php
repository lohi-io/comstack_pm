<?php

/**
 * @file
 * Contains ComstackPMMessagesResource__1_0.
 */

class ComstackPMMessagesResource__1_0 extends \ComstackRestfulEntityBase {
  protected $cursor_paging = TRUE;

  /**
   * Overrides \RestfulEntityBase::controllersInfo().
   */
  public static function controllersInfo() {
    return array(
      '^(\d+,)*\d+$' => array(
        \RestfulInterface::GET => 'viewEntity',
        \RestfulInterface::PATCH => 'patchEntity',
      ),
    );
  }

  /**
   * Overrides \RestfulEntityBase::checkEntityAccess().
   *
   * As the message entity has it's own access callback which we'll opt to
   * ignore as within the Comstack PM context the rules change.
   *
   * message_access() only checks that the user has access to create new
   * messages. $op can be "create", "update" and "delete". This class adds "view"
   */
  protected function checkEntityAccess($op, $entity_type, $entity) {
    $account = $this->getAccount();

    return comstack_pm_message_access($op, $entity, $account);
  }

  /**
   * Overrides \RestfulEntityBase::getQueryForList().
   *
   * Only expose messages which haven't been deleted.
   */
  public function getQueryForList() {
    $query = parent::getQueryForList();

    if (!user_access('view deleted comstack messages', $this->getAccount())) {
      $query->propertyCondition('deleted', 0);
    }

    return $query;
  }

  /**
   * Overrides \RestfulDataProviderEFQ::getQueryCount().
   */
  public function getQueryCount() {
    $query = parent::getQueryCount();

    if (!user_access('view deleted comstack messages', $this->getAccount())) {
      $query->propertyCondition('deleted', 0);
    }

    return $query->count();
  }

  /**
   * Overrides \RestfulEntityBase::publicFieldsInfo().
   */
  public function publicFieldsInfo() {
    $public_fields = parent::publicFieldsInfo();

    // Remove ID as this requires the permission "create messages" to perform
    // any operation against it, therefore is moot. So the module doesn't
    // require that this permission is given to everyone, just map the ID to
    // mid which seems to bypass all this, as we're saying schema thing, not
    // entity ID.
    unset($public_fields['id']);

    $public_fields['type'] = array(
      'callback' => array('\RestfulManager::echoMessage', array('message')),
    );

    $public_fields['id'] = array(
      'property' => 'mid',
    );

    $public_fields['message_type'] = array(
      'property' => 'type',
      // Get the value straight from the {message} table.
      // http://www.drupalcontrib.org/api/drupal/contributions%21entity%21includes%21entity.wrapper.inc/function/EntityMetadataWrapper%3A%3Araw/7
      'wrapper_method' =>  'raw',
    );

    $public_fields['conversation_id'] = array(
      'property' => 'cs_pm_conversation',
      'wrapper_method' =>  'raw',
      'process_callbacks' => array(
        'intval',
      ),
    );

    $public_fields['sender'] = array(
      'property' => 'user',
      // Instead of getting a lump of an entity, use the Comstack PM Users
      // resource to format the user entity. Do this by specifying the bundle
      // and the "name" of resource, which is the path? :/
      'resource' => array(
        'user' => array(
          'name' => 'cs-pm/users',
          'full_view' => TRUE,
        ),
      ),
    );

    $public_fields['sent'] = array(
      'property' => 'timestamp',
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

    $public_fields['text'] = array(
      'property' => 'cs_pm_text',
      'sub_property' => 'value',
    );

    $public_fields['weight'] = array(
      'property' => 'weight',
    );

    $public_fields['edits'] = array(
      'property' => 'edits',
    );

    $public_fields['deleted'] = array(
      'property' => 'deleted',
    );

    unset($public_fields['label']);
    unset($public_fields['self']);

    return $public_fields;
  }

  /**
   * Overrides \RestfulEntityBase::patchEntity().
   *
   * Only allow the "text" property to be modified.
   */
  public function patchEntity($entity_id) {
    // Loop through the exposed properties and remove all except "text".
    $request = $this->getRequest();
    foreach ($this->getPublicFields() as $public_field_name => $info) {
      if (isset($request[$public_field_name]) && $public_field_name !== 'text') {
        unset($request[$public_field_name]);
      }
    }
    $this->setRequest($request);
    unset($request);

    return $this->updateEntity($entity_id, FALSE);
  }

  /**
   * Overrides \RestfulEntityBase::propertyValuesPreprocessText().
   *
   * The input format is hardcoded into this function, use a variable instead.
   */
  protected function propertyValuesPreprocessText($property_name, $value, $field_info) {
    $value = trim($value);

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
}
