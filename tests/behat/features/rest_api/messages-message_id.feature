Feature: GET a message and Update the message text, as Authenticated user.

  @api @restapi @get @expectsvalid
  Scenario: GET an existing message by ID.
    Given I'm logged in as testy
    And I have an access token
    When I request "GET /api/v1/cs-pm/messages/1"
    Then The REST API returns a 200 response
    And scope into the "data" property
    And the properties exist:
    """
    type
    id
    message_type
    conversation_id
    sender
    sent
    updated
    text
    weight
    edits
    """

  @api @restapi @get @expectsinvalid
  Scenario: Attempt to GET a message by ID which doesn't exist.
    Given I'm logged in as testy
    And I have an access token
    When I request "GET /api/v1/cs-pm/messages/999999"
    Then The REST API returns a 404 response

  @api @restapi @patch @expectsvalid
  Scenario: Update an existing message's text.
    Given I'm logged in as testy
    And I have an access token
    And I have the payload:
    """
    {
      "text": "Sample text"
    }
    """
    When I request "PATCH /api/v1/cs-pm/messages/1"
    Then The REST API returns a 200 response

  @api @restapi @patch @expectsvalid
  Scenario: Update an existing message but without any data.
    Given I'm logged in as testy
    And I have an access token
    When I request "PATCH /api/v1/cs-pm/messages/1"
    Then The REST API returns a 400 response

  @api @restapi @patch @expectsinvalid
  Scenario: Update a message which doesn't exist.
    Given I'm logged in as testy
    And I have an access token
    And I have the payload:
    """
    {
      "text": "Sample text"
    }
    """
    When I request "PATCH /api/v1/cs-pm/messages/99999"
    Then The REST API returns a 404 response
