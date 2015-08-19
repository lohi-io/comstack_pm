Feature: List a users conversations and Create a new conversation.

  @api
  Scenario: Valid response.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations"
    Then The REST API returns a 200 response
    And scope into the "data" property
    And the properties exist:
    """
    type
    id
    participants
    historical_participants
    started_by
    last_updated_by
    started
    updated
    messages_count
    unread_count
    """

    #204 No content (no conversations exist for this user).

  @api
  Scenario: Content successfully created.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    Given I have the payload:
    """
    {
    "recipients": "1988",
    "text": "Sample text"
    }
    """
    When I request "POST /api/v1/cs-pm/conversations"
    Then The REST API returns a 201 response

  @api
  Scenario: Start a new conversation without a text.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "POST /api/v1/cs-pm/conversations"
    Then The REST API returns a 400 response
