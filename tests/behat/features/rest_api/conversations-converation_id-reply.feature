Feature: Add a reply to a conversation, as Authenticated user.

  @api @restapi @post @expectsvalid
  Scenario: Attempt to add a reply to a conversation.
    Given I'm logged in as testy
    And I have an access token
    And I have the payload:
    """
    {
      "text": "Blah blah"
    }
    """
    When I request "POST /api/v1/cs-pm/conversations/1/reply"
    Then The REST API returns a 201 response


  @api @restapi @post @expectsinvalid
  Scenario: Attempt to add a reply without any text.
    Given I'm logged in as testy
    And I have an access token
    When I request "POST /api/v1/cs-pm/conversations/1/reply"
    Then The REST API returns a 400 response

  @api @restapi @post @expectsinvalid
  Scenario: Attempt to add a reply to a conversation that doesn't exist.
    Given I'm logged in as testy
    And I have an access token
    And I have the payload:
    """
    {
      "text": "Blah blah"
    }
    """
    When I request "POST /api/v1/cs-pm/conversations/999999/reply"
    Then The REST API returns a 404 response
