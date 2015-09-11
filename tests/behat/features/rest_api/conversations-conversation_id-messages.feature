Feature: GET messages that belong to a conversation, as Authenticated user.

  Background: Logged in as Basic user

  @api @restapi @post @expectsvalid
  Scenario: Content successfully created.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    Given I have the payload:
    """
    {
    "recipients": "[33562]",
    "text": "Blah blah"
    }
    """
    When I request "POST /api/v1/cs-pm/conversations"
    Then The REST API returns a 201 response

 @api @restapi @get @expectsvalid
 Scenario: GET messages from a conversation which exists.
    Given I am logged in as a user with the authenticated role
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
    And the "conversation_id" property is an integer equalling "1"
    And the "text" property is a string equalling "Blah blah"

 @api @restapi @get @expectsinvalid
 Scenario: Attempt to GET messages from an empty conversation.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations/1/messages"
    Then The REST API returns a 204 response

 @api @restapi @get @expectsinvalid
 Scenario: Attempt to get messages from a conversation that doesn't exist.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations/99999/messages"
    Then The REST API returns a 404 response
