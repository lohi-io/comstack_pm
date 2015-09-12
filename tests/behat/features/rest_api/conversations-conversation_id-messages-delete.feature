Feature: Delete messages that belong to a conversation, as Authenticated user.

  @api @restapi @post @expectsvalid
  Scenario: Delete a message from a conversation.
    Given I am logged in as testy
    And I have an access token
    And I have the payload:
    """
    {
      "ids": [1]
    }
    """
    When I request "POST /api/v1/cs-pm/conversations/1/messages/delete"
    Then The REST API returns a 200 response

  @api @restapi @post @expectsinvalid
  Scenario: Attempt to delete messages without sending any ids, an invalid request.
    Given I am logged in as testy
    And I have an access token
    When I request "POST /api/v1/cs-pm/conversations/1/messages/delete"
    Then The REST API returns a 400 response

  @api @restapi @post @expectsinvalid
  Scenario: Attempt to delete messages from a conversation which doesn't exist.
    Given I am logged in as testy
    And I have an access token
    And I have the payload:
    """
    {
      "ids": [1]
    }
    """
    When I request "POST /api/v1/cs-pm/conversations/99999/messages/delete"
    Then The REST API returns a 404 response
