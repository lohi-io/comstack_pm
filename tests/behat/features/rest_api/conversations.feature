Feature: List a users conversations and Create a new conversation.

  @api @restapi @post @expectsvalid @runfirst
  Scenario: Start a new conversation.
    Given I'm logged in as testy
    And I have an access token
    And I have a CSRF token
    And I have the payload:
    """
    {
    "recipients": [1,2],
    "text": "Blah blah"
    }
    """
    When I request "POST /api/v1/cs-pm/conversations"
    Then The REST API returns a 201 response
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
    container
    title
    unread_count
    pinned
    archived
    muted
    starred
    forwarded
    deleted
    """

  @api @restapi @post @expectsinvalid
  Scenario: Start a new conversation without any text.
    Given I'm logged in as testy
    And I have an access token
    When I request "POST /api/v1/cs-pm/conversations"
    Then The REST API returns a 400 response

  @api @restapi @get @expectsvalid
  Scenario: Validating the GET response
    Given I'm logged in as testy
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations"
    Then The REST API returns a 200 response
    And scope into the first "data" property
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
    container
    title
    unread_count
    pinned
    archived
    muted
    starred
    forwarded
    deleted
    """
    And the "type" property is a string equalling "user"
    And the "id" property is an integer equalling "1"
    And the "participants" property is an array
    And the "historical_participants" property is an array
    And the "started_by" property is an object
    And the "last_updated_by" property is an object
    And the "unread_count" property equals "0"
    And the "pinned" property is a boolean equalling "false"
    And the "archived" property is a boolean equalling "false"
    And the "muted" property is a boolean equalling "false"
    And the "starred" property is a boolean equalling "false"
    And the "forwarded" property is a boolean equalling "false"
    And the "deleted" property is a boolean equalling "false"
