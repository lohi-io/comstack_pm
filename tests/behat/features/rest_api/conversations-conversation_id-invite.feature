Feature: Invite a user to the conversation, as Authenticated user.

 @api @restapi @post @expectsvalid
 Scenario: The user should be able to invite a valid user to an existing conversation.
    Given I'm logged in as testy
    And I have an access token
    And I have the payload:
    """
    {
      "ids": [3],
    }
    """
    When I request "POST /api/v1/cs-pm/conversations/1/invite"
    Then The REST API returns a 200 response

 @api @restapi @post @expectsinvalid
 Scenario: Invalid request to invite users to a conversation without sending user ids.
    Given I'm logged in as testy
    And I have an access token
    When I request "POST /api/v1/cs-pm/conversations/1/invite"
    Then The REST API returns a 400 response

 @api @restapi @post @expectsinvalid
 Scenario: Invite a user to a conversation which doesn't exist.
    Given I'm logged in as testy
    And I have an access token
    And I have the payload:
    """
    {
      "ids": [3],
    }
    """
    When I request "POST /api/v1/cs-pm/conversations/99999/invite"
    Then The REST API returns a 404 response
