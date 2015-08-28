<?php

/**
 * @file
 * Contains ComstackPMAvailableUsersResource__1_0.
 */

class ComstackPMAvailableUsersResource__1_0 extends \ComstackPMUsersResource__1_0 {
  // Set the default range.
  protected $range = 25;

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
}
