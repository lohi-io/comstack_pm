Feature: Removing the valid user from the conversation, as Authenticated user.

  @api @restapi @post @expectsvalid
  Scenario: Content successfully created.
    Given I am logged in as testy
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

  @api @restapi @put @expectsvalid
  Scenario: Allow the current user to leave a conversation.
    Given I am logged in as testy
    And I have an access token
    When I request "PUT /api/v1/cs-pm/conversations/1/leave"
    Then The REST API returns a 200 response

  @api @restapi @put @expectsvalid
  Scenario: Attempt to leave a conversation which doesn't exist.
    Given I am logged in as testy
    And I have an access token
    When I request "PUT /api/v1/cs-pm/conversations/99999/leave"
    Then The REST API returns a 404 response
