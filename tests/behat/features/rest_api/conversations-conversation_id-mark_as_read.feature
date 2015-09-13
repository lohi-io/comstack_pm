Feature: Mark a conversation as read, no unread messages within, as Authenticated user.

  @api @restapi @put @expectsvalid
  Scenario: Attempt to mark a conversation as read.
    Given I'm logged in as testy
    And I have an access token
    When I request "PUT /api/v1/cs-pm/conversations/1/mark-as-read"
    Then The REST API returns a 200 response

  @api @restapi @put @expectsinvalid
  Scenario: Attempt to mark a conversation which doesn't exist as read.
    Given I'm logged in as testy
    And I have an access token
    When I request "PUT /api/v1/cs-pm/conversations/99999/mark-as-read"
    Then The REST API returns a 404 response
