<?php

/**
 * @file
 * Contains ComstackPMMessagesResource__1_0.
 */

class ComstackPMMessagesResource__1_0 extends \RestfulEntityBase {
  /**
   * Overrides \RestfulEntityBase::controllersInfo().
   */
  public static function controllersInfo() {
    return array(
      '^(\d+,)*\d+$' => array(
        \RestfulInterface::GET => 'viewEntities',
        \RestfulInterface::PATCH => 'patchEntity',
      ),
    );
  }

  /**
   * Overrides \RestfulEntityBase::getQueryForList().
   *
   * Only expose messages which haven't been deleted.
   */
  public function getQueryForList() {
    $query = parent::getQueryForList();
    $query->propertyCondition('deleted', 0);
    return $query;
  }

  /**
   * Overrides \RestfulDataProviderEFQ::getQueryCount().
   */
  public function getQueryCount() {
    $query = parent::getQueryCount();
    $query->propertyCondition('deleted', 0);
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
      'callback' => array('\RestfulManager::echoMessage', array('message')),
    );

    $public_fields['id'] = $id_field;

    $public_fields['message_type'] = array(
      'property' => 'type',
      // Get the straight value from the {message} table.
      // http://www.drupalcontrib.org/api/drupal/contributions%21entity%21includes%21entity.wrapper.inc/function/EntityMetadataWrapper%3A%3Araw/7
      'wrapper_method' =>  'raw',
    );

    $public_fields['conversation_id'] = array(
      'property' => 'cs_pm_conversation',
      'wrapper_method' =>  'raw',
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
}
