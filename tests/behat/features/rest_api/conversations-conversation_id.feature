Feature: Test the endpoint for specific conversations with the available HTTP methods - GET and DELETE.

 @api @restapi @get @expectsvalid
 Scenario: Authenticated user session.
    Given I'm logged in as testy
    And I have an access token
    And I have a CSRF token
    When I request "GET /api/v1/cs-pm/conversations/1"
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
    last_message
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
    And the "id" property is a integer equalling "1"
    And the "participants" property is an array
    And the "historical_participants" property is an array
    And the "started_by" property is an object
    And the "last_updated_by" property is an object
    And the "last_message" property is an object
    And the "unread_count" property equals "0"
    And the "pinned" property is a boolean equalling "false"
    And the "archived" property is a boolean equalling "false"
    And the "muted" property is a boolean equalling "false"
    And the "starred" property is a boolean equalling "false"
    And the "forwarded" property is a boolean equalling "false"
    And the "deleted" property is a boolean equalling "false"

 @api @restapi @get @expectsinvalid
 Scenario: Attempt to load a conversation which doesn't exist.
    Given I'm logged in as testy
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations/99999"
    Then The REST API returns a 404 response

 @api @restapi @delete @expectsvalid @runlast
 Scenario: Delete a conversation.
    Given I'm logged in as testy
    And I have an access token
    And I have a CSRF token
    When I request "DELETE /api/v1/cs-pm/conversations/1"
    Then The REST API returns a 200 response
    And I request "GET /api/v1/cs-pm/conversations/1"
    And The REST API returns a 404 response

 @api @restapi @delete @expectsinvalid
 Scenario: Attempt to DELETE a conversation which doesn't exist.
    Given I'm logged in as testy
    And I have an access token
    When I request "DELETE /api/v1/cs-pm/conversations/99999"
    Then The REST API returns a 404 response
