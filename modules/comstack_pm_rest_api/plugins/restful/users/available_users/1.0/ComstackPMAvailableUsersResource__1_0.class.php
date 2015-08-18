<?php

/**
 * @file
 * Contains ComstackPMAvailableUsersResource__1_0.
 */

class ComstackPMAvailableUsersResource__1_0 extends \RestfulEntityBaseUser {
  // Set the default range.
  //$this->range = 50;

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
   * Overrides \RestfulEntityBase::defaultSortInfo().
   */
  public function defaultSortInfo() {
    // Sort by 'id' in descending order.
    return array('name' => 'ASC');
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
      'callback' => 'static::getResourceType',
    );

    $public_fields['id'] = $id_field;

    $public_fields['name'] = array(
      'property' => 'name',
    );

    $public_fields['avatars'] = array(
      'property' => 'picture',
      'process_callbacks' => array(
        // First array is for the callback, second for parameter to pass to the
        // callback. The field value is automatically passed as the first
        // parameter.
        // If this were a regular image field then we could use the following
        // style of code...
        // https://github.com/RESTful-Drupal/restful/blob/7.x-1.x/modules/restful_example/plugins/restful/node/articles/1.5/RestfulExampleArticlesResource__1_5.class.php#L28
        array(
          array($this, 'userPictureProcess'),
          array(array('comstack-100-100', 'comstack-200-200')),
        ),
      ),
    );

    // @todo this should be a count of the number of time the current user has
    // contacted the user in question.
    $public_fields['contact_frequency'] = array(
      'callback' => 'static::getContactFrequency',
    );

    // Remove default properties we don't want, yeah self. Discoverable
    // dischmoverable. This will be revisited and done properly at a later
    // date. @todo expand API documentation with HAL self stuff.
    unset($public_fields['label']);
    unset($public_fields['mail']);
    unset($public_fields['self']);

    return $public_fields;
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
    return $query;
  }

  /**
   * Overrides \RestfulDataProviderEFQ::getQueryCount().
   */
  public function getQueryCount() {
    $query = parent::getQueryCount();
    $query->addTag('comstack_recipients');

    return $query->count();
  }

  /**
   * Get the "type" value, just a static string.
   *
   * @param \EntityMetadataWrapper $wrapper
   *   The wrapped entity.
   *
   * @return string
   *   The type name of this data.
   */
  public static function getResourceType(\EntityMetadataWrapper $wrapper) {
    return 'user';
  }

  /**
   * Get the contact frequency between the current user and the user in this
   * row.
   *
   * @param \EntityMetadataWrapper $wrapper
   *   The wrapped entity.
   *
   * @return integer
   *   The type name of this data.
   */
  public static function getContactFrequency(\EntityMetadataWrapper $wrapper) {
    return 0;
  }

  /**
   * Process callback for user image.
   *
   * @param object $file
   *   The image object.
   * @param array $image_styles
   *   An array of image styles to apply the to image.
   *
   * @return array
   *   A cleaned image array.
   */
  public function userPictureProcess($file, array $image_styles) {
    // If there's no image to process, bail out.
    if (!is_object($file) || !$file || empty($image_styles)) {
      return;
    }

    // Loop through the image styles.
    $output = array();
    foreach ($image_styles as $style) {
      $url = image_style_url($style, $file->uri);

      if ($url) {
        // Replace "comstack-" with nothing from the image style name.
        $key_name = str_replace('comstack-', '', $style);
        $output[$key_name] = $url;
      }
    }

    return $output;
  }
}
