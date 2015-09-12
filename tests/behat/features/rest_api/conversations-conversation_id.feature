Feature: Test the endpoint for specific conversations with the available HTTP methods - GET and DELETE.

 @api @restapi @get @expectsvalid
 Scenario: Authenticated user session.
    Given I am logged in as testy
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations/1"
    Then I should get a 200 HTTP response
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

 @api @restapi @get @expectsinvalid
 Scenario: Authenticated user session with wrong conversation ID
    Given I am logged in as testy
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations/9"
    Then I should get a 404 HTTP response

 @api @restapi @delete @expectsvalid
 Scenario: Authenticated user session.
    Given I am logged in as testy
    And I have an access token
    When I request "DELETE /api/v1/cs-pm/conversations/1"
    Then I should get a 200 HTTP response

 @api @restapi @delete @expectsinvalid
 Scenario: Authenticated user session with wrong conversation ID
    Given I am logged in as testy
    And I have an access token
    When I request "DELETE /api/v1/cs-pm/conversations/99"
    Then I should get a 404 HTTP response
