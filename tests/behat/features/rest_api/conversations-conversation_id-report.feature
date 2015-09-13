Feature: Report a conversation as Authenticated user.

  @api @restapi @post @expectsvalid
  Scenario: Report a conversation.
    Given I'm logged in as testy
    And I have an access token
    And I have the payload:
    """
    {
      "reasons": [3],
      "other_reason": "Any old text!",
      "posts": [1]
    }
    """
    When I request "POST /api/v1/cs-pm/conversations/1/report"
    Then The REST API returns a 201 response

  @api @restapi @post @expectsinvalid
  Scenario: Attempt to report a conversation without sending any detail/reason data.
    Given I'm logged in as testy
    And I have an access token
    When I request "POST /api/v1/cs-pm/conversations/1/report"
    Then The REST API returns a 400 response

  @api @restapi @post @expectsinvalid
  Scenario: Attempt to report a conversation which doesn't exist.
    Given I'm logged in as testy
    And I have an access token
    And I have the payload:
    """
    {
      "reasons": [3],
      "other_reason": "Any old text!",
      "posts": [1]
    }
    """
    When I request "POST /api/v1/cs-pm/conversations/99999/report"
    Then The REST API returns a 404 response
