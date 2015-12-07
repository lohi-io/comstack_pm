<?php

/**
 * @file
 * Contains comstack_pm_handler_field_entity_ref_labels.
 */

class comstack_pm_handler_field_entity_ref_labels extends views_handler_field {
  public $link_to_entities = FALSE;

  /**
   * Make views load up the conversation id for us.
   */
  function construct() {
    parent::construct();

    $this->real_field = 'conversation_id';
  }

  /**
   * Grab the entity ref field we're targeting to output.
   */
  function getField() {
    return $this->definition['target_field'];
  }

  /**
   * Determine if the current user should have the entity labels be linked or
   * not.
   */
  function allowEntityLink() {
    $permissions = $this->definition['link_permissions'];

    foreach ($permissions as $permission) {
      if (user_access($permission)) {
        return TRUE;
        break;
      }
    }

    return FALSE;
  }

  /**
   * If we've got values then check if we can link to them.
   */
  function pre_render(&$values) {
    if (!empty($values)) {
      $this->link_to_entities = $this->allowEntityLink();

      /**
       * At this point other handlers would pre load the entities with
       * entity_load() though we don't as Views will have already grabbed
       * them.
       */
    }
  }

  /**
   * Output the values of the field.
   */
  function render($values) {
    if ($conversation_id = $this->get_value($values)) {
      /**
       * Load the conversation without point of view context.
       */
      $conversation = comstack_conversation_load($conversation_id);

      if ($conversation) {
        $wrapper = entity_metadata_wrapper('comstack_conversation', $conversation);
        $field = $this->getField();

        if ($wrapper->__isset($field) && $wrapper->{$field}->value()) {
          $output = array();

          foreach ($wrapper->{$field}->getIterator() as $delta => $entity_wrapper) {
            $label = check_plain($entity_wrapper->label());

            if ($this->link_to_entities) {
              $entity = $entity_wrapper->value();
              $uri = entity_uri($entity_wrapper->type(), $entity);
              $label = l($label, $uri['path'], array('html' => TRUE));
            }

            $output[] = $label;
          }

          if (!empty($output)) {
            return implode(', ', $output);
          }
        }
      }
    }
  }
}
