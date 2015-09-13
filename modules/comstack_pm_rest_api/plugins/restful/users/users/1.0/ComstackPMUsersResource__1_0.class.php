<?php

/**
 * @file
 * Contains ComstackPMUsersResource__1_0.
 */

class ComstackPMUsersResource__1_0 extends \RestfulEntityBaseUser {
  /**
   * Overrides \RestfulDataProviderEFQ::controllersInfo().
   */
  public static function controllersInfo() {
    return array(
      '^(\d+,)*\d+$' => array(
        // Only allow API users to GET a single user entity.
        \RestfulInterface::GET => 'viewEntities',
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

    /**
     * We want to simply output a string, if we want to do this then we could
     * do it as following with a static callback method within this class.
     */
    /*$public_fields['type'] = array(
      'callback' => 'static::getResourceType',
    );*/
    /**
     * Simplest way is to use a method in an ancestor class.
     */
    $public_fields['type'] = array(
      'callback' => array('\RestfulManager::echoMessage', array('user')),
    );

    $public_fields['id'] = $id_field;
    // Force correct data type on output.
    $public_fields['id']['process_callbacks'][] = 'intval';

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
      'callback' => array('\RestfulManager::echoMessage', array(0)),
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
