Feature: Removing the valid user from the conversation, as Authenticated user.

  @api @restapi @put @expectsvalid @runlast
  Scenario: Allow the current user to leave a conversation.
    Given I'm logged in as testy
    And I have an access token
    When I request "PUT /api/v1/cs-pm/conversations/1/leave"
    Then The REST API returns a 200 response

  @api @restapi @put @expectsvalid
  Scenario: Attempt to leave a conversation which doesn't exist.
    Given I'm logged in as testy
    And I have an access token
    When I request "PUT /api/v1/cs-pm/conversations/99999/leave"
    Then The REST API returns a 404 response
