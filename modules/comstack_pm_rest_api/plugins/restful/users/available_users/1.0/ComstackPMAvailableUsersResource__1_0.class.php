<?php

/**
 * @file
 * Contains ComstackPMAvailableUsersResource__1_0.
 */

class ComstackPMAvailableUsersResource__1_0 extends \ComstackUsersResource__1_0 {
  // Set the default range.
  protected $range = 50;

  /**
   * Overrides \RestfulDataProviderEFQ::controllersInfo().
   * https://github.com/RESTful-Drupal/restful/blob/f0f981a8552a8c8b429bce2991d06e551661adff/tests/modules/restful_test/plugins/restful/node/test_articles/1.4/RestfulTestArticlesResource__1_4.class.php#L10-L23
   */
  public static function controllersInfo() {
    // Restrict which HTTP methods this endpoint will respond to.
    return array(
      '' => array(
        // GET returns a list of entities.
        \RestfulInterface::GET => 'getList',
        \RestfulInterface::HEAD => 'getList',
      ),
    );
  }

  /**
   * Overrides \RestfulEntityBaseUser::getList().
   *
   * Remove the access check instated by RestfulEntityBaseUser.
   */
  public function getList() {
    return RestfulEntityBase::getList();
  }

  /**
   * Overrides \RestfulEntityBase::getQueryForList().
   *
   * Add the comstack_recipients tag to the query.
   */
  public function getQueryForList() {
    $query = parent::getQueryForList();
    // Add the comstack recipients tag so that other modules can jump in and
    // alter the query (restrict available users).
    $query->addTag('comstack_recipients');

    // Check that the people this user is friends with has private messaging
    // enabled.
    if (variable_get('comstack_pm_preferences__enabled__provide', FALSE) && module_exists('user_preferences')) {
      $query->addTag('comstack_pm_preferences__enabled');
    }

    return $query;
  }

  /**
   * Overrides \RestfulDataProviderEFQ::getQueryCount().
   */
  public function getQueryCount() {
    $query = parent::getQueryCount();
    $query->addTag('comstack_recipients');

    // Check that the people this user is friends with has private messaging
    // enabled.
    if (variable_get('comstack_pm_preferences__enabled__provide', FALSE) && module_exists('user_preferences')) {
      $query->addTag('comstack_pm_preferences__enabled');
    }

    return $query->count();
  }

  /**
   * Overrides \RestfulEntityBase::getQueryForAutocomplete().
   */
  protected function getQueryForAutocomplete() {
    $query = parent::getQueryForAutocomplete();

    $query->addTag('comstack_recipients');

    // Check that the people this user is friends with has private messaging
    // enabled.
    if (variable_get('comstack_pm_preferences__enabled__provide', FALSE) && module_exists('user_preferences')) {
      $query->addTag('comstack_pm_preferences__enabled');
    }

    return $query;
  }

  /**
   * Overrides \RestfulEntityBase::getQueryResultForAutocomplete().
   */
  protected function getQueryResultForAutocomplete() {
    $entity_type = $this->getEntityType();
    $query = $this->getQueryForAutocomplete();
    $result = $query->execute();

    if (empty($result[$entity_type])) {
      // No entities found.
      return array();
    }

    $ids = array_keys($result[$entity_type]);

    // Pre-load all entities if there is no render cache.
    $cache_info = $this->getPluginKey('render_cache');
    if (!$cache_info['render']) {
      entity_load($entity_type, $ids);
    }

    $return = array();

    // If no IDs were requested, we should not throw an exception in case an
    // entity is un-accessible by the user.
    foreach ($ids as $id) {
      if ($row = $this->viewEntity($id)) {
        $return[] = $row;
      }
    }

    return $return;
  }

  /**
   * Overrides \RestfulEntityBase::getListForAutocomplete().
   */
  protected function getListForAutocomplete() {
    $entity_info = $this->getEntityInfo();
    if (empty($entity_info['entity keys']['label'])) {
      // Entity is invalid for autocomplete, as it doesn't have a "label"
      // property.
      $params = array('@entity' => $this->getEntityType());
      throw new \Exception(format_string('Cannot autocomplete @entity as it does not have a "label" property defined.', $params));
    }

    $request = $this->getRequest();
    if (empty($request['autocomplete']['string'])) {
      // Empty string.
      return array();
    }

    $return = $this->getQueryResultForAutocomplete();

    return $return;
  }
}
