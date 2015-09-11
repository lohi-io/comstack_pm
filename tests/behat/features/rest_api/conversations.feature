Feature: List a users conversations and Create a new conversation.

  Background: Logged in as Basic user

  @api
  Scenario: Start a conversation with payload.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    And I have a CSRF token
    And I have the payload:
    """
    {
    "recipients": "[??]",
    "text": "Blah blah"
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

  @api
  Scenario: Validating the GET response
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
    Started
    Updated
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
