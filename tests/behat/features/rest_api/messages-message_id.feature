Feature: GET a message and Update the message text, as Authenticated user.

  @api
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

  @api
  Scenario: GET an existing message by ID.
    Given I am logged in as a user with the authenticated role
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

  @api
  Scenario: Attempt to GET a message by ID which doesn't exist.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/messages/999999"
    Then The REST API returns a 404 response

  @api
  Scenario: Update an existing message's text.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    And I have the payload:
    """
    {
      "text": "Sample text"
    }
    """
    When I request "PUT /api/v1/cs-pm/messages/1"
    Then The REST API returns a 200 response

  @api
  Scenario: Update an existing message but without any data.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "PUT /api/v1/cs-pm/messages/1"
    Then The REST API returns a 400 response

  @api
  Scenario: Update a message which doesn't exist.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    And I have the payload:
    """
    {
      "text": "Sample text"
    }
    """
    When I request "PUT /api/v1/cs-pm/messages/99999"
    Then The REST API returns a 404 response
