Feature: Mark a conversation as unread, as an Authenticated user.

  @api @restapi @post @expectsvalid
  Scenario: Content successfully created.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    Given I have the payload:
    """
    {
    "recipients": [1,2],
    "text": "Sample text"
    }
    """
    When I request "POST /api/v1/cs-pm/conversations"
    Then The REST API returns a 201 response

  @api @restapi @delete @expectsvalid
  Scenario: Attempt to mark a conversation as unread.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "DELETE /api/v1/cs-pm/conversations/1/mark-as-read"
    Then The REST API returns a 200 response

  @api @restapi @delete @expectsinvalid
  Scenario: Attempt to mark a conversation which doesn't exist as unread.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "DELETE /api/v1/cs-pm/conversations/99999/mark-as-read"
    Then The REST API returns a 404 response
