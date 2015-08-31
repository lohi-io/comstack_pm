<?php


/**
 * @file
 * Contains ComstackRestfulEntityBase.
 */

/**
 * An abstract extension of of RestfulEntityBase.
 *
 * The purpose of this class is to optionally do all of the work needed for
 * cursor paging to work, though we also need a formatter as well.
 *
 * @see \
 * http://www.sitepoint.com/paginating-real-time-data-cursor-based-pagination
 */
abstract class ComstackRestfulEntityBase extends \RestfulEntityBase {
  protected $cursor_paging = FALSE;
  protected $cursor_paging_data = array();
  protected $wildcard_entity_id = NULL;

  /**
   * Overrides \RestfulBase::addCidParams().
   *
   * Make this function sensitive to our new pagination parameters if in use.
   */
  protected static function addCidParams($keys) {
    $cid_params = array();
    $request_params_to_ignore = array(
      '__application',
      'filter',
      'loadByFieldName',
      'q',
      'range',
      'sort',
    );

    if (!$this->cursor_paging) {
      $request_params_to_ignore[] = 'page';
    }
    else {
      $request_params_to_ignore[] = 'after';
      $request_params_to_ignore[] = 'before';
    }

    foreach ($keys as $param => $value) {
      if (in_array($param, $request_params_to_ignore)) {
        continue;
      }
      $values = explode(',', $value);
      sort($values);
      $value = implode(',', $values);

      $cid_params[] = substr($param, 0, 2) . ':' . $value;
    }
    return $cid_params;
  }

  /**
   * Overrides \RestfulDataProviderEFQ::queryForListPagination().
   */
  protected function queryForListPagination(\EntityFieldQuery $query) {
    list($offset, $range) = $this->parseRequestForListPagination();

    // Normal pager stuff.
    if (!$this->cursor_paging) {
      $query->range($offset, $range);
    }
    // Cursor paging!
    else {
      $request = $this->getRequest();
      $after = isset($request['after']) && ctype_digit($request['after']) ? $request['after'] : NULL;
      $before = isset($request['before']) && ctype_digit($request['before']) ? $request['before'] : NULL;

      if ($after) {
        $query->entityCondition('entity_id', $after, '>');
      }
      if ($before) {
        $query->entityCondition('entity_id', $before, '<');
      }

      // Add limit from range.
      $query->range(0, $range);
    }
  }

  /**
   * Overrides \RestfulEntityBase::getList().
   */
  public function getList() {
    $request = $this->getRequest();
    $autocomplete_options = $this->getPluginKey('autocomplete');
    if (!empty($autocomplete_options['enable']) && isset($request['autocomplete']['string'])) {
      // Return autocomplete list.
      return $this->getListForAutocomplete();
    }

    $entity_type = $this->entityType;
    $result = $this
      ->getQueryForList()
      ->execute();

    if (empty($result[$entity_type])) {
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

    // Throw in pagination data.
    if ($this->cursor_paging && !empty($ids)) {
      $first_id = $ids[0];
      $last_id = $ids[count($ids) - 1];

      $is_first_page = !isset($request['after']) && !isset($request['before']);

      // Alter the request so generated URLs are for other pages.
      $request['after'] = $last_id;
      $request['before'] = $first_id;

      $cursor_paging_data = array(
        'paging_cursors' => array(
          'after' => $last_id,
          'before' => $first_id,
        ),
        'previous' => NULL,
        'next' => NULL,
      );

      // Provide a "Previous" paging link if we're not on the first page.
      // If before or after haven't been specified in the request then it's
      // fair to assume this is the first page.
      if (!$is_first_page) {
        unset($request['after']);
        $request['before'] = $first_id;
        $cursor_paging_data['previous'] = array(
          'title' => t('Previous'),
          'href' => $this->getUrl($request),
        );
      }

      // Provide a "Next" paging link if there's more data to have.
      if ($this->getRange() == count($ids)) {
        unset($request['before']);
        $request['after'] = $last_id;
        $cursor_paging_data['next'] = array(
          'title' => t('Next'),
          'href' => $this->getUrl($request),
        );
      }

      $this->cursor_paging_data = $cursor_paging_data;
    }

    return $return;
  }

  /**
   * Add paging information to the returned array if using cursor paging.
   *
   */
  public function additionalHateoas() {
    $data = array();

    if ($this->cursor_paging) {
      $data = $this->cursor_paging_data;
    }

    return $data;
  }

  /**
   * Overrides \RestfulBase::versionedUrl().
   *
   * Alter the URL if there's a wildcard in it.
   */
  public function versionedUrl($path = '', $options = array(), $version_string = TRUE) {
    // Make the URL absolute by default.
    $options += array('absolute' => TRUE);
    $plugin = $this->getPlugin();
    if (!empty($plugin['menu_item'])) {
      $url = $plugin['menu_item'] . '/' . $path;
      return url(rtrim($url, '/'), $options);
    }

    // If there's an entity ID and a wildcard in the resource path replace it.
    if ($this->wildcard_entity_id && strpos($plugin['resource'], '%') !== FALSE) {
      $plugin['resource'] = str_replace('%', $this->wildcard_entity_id, $plugin['resource']);
    }

    $base_path = variable_get('restful_hook_menu_base_path', 'api');
    $url = $base_path;
    if ($version_string) {
      $url .= '/v' . $plugin['major_version'] . '.' . $plugin['minor_version'];
    }
    $url .= '/' . $plugin['resource'] . '/' . $path;
    return url(rtrim($url, '/'), $options);
  }
}
