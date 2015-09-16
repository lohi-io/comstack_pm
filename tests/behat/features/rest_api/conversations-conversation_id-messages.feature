Feature: GET messages that belong to a conversation, as Authenticated user.

 @api @restapi @get @expectsvalid
 Scenario: GET messages from a conversation which exists.
    Given I'm logged in as testy
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations/1/messages"
    Then The REST API returns a 200 response
    And scope into the first "data" property
    And the properties exist:
    """
    type
    id
    conversation_id
    sender
    sent
    updated
    text
    weight
    edits
    deleted
    """
    And the "type" property is a string equalling "message"
    And the "conversation_id" property is a integer equalling "1"
    And the "text" property is a string which contains "<p>Blah blah<p>"

 @api @restapi @get @expectsinvalid
 Scenario: Attempt to get messages from a conversation that doesn't exist.
    Given I'm logged in as testy
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations/99999/messages"
    Then The REST API returns a 404 response
