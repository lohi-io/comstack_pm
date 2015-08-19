Feature: Mark a conversation as unread, as an Authenticated user.

  @api
  Scenario: Attempt to mark a conversation as unread.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "DELETE /api/v1/cs-pm/conversations/1/mark-as-read"
    Then The REST API returns a 200 response

  @api
  Scenario: Attempt to mark a conversation which doesn't exist as unread.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "DELETE /api/v1/cs-pm/conversations/99999/mark-as-read"
    Then The REST API returns a 404 response
