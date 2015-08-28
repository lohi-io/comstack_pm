Feature: GET messages that belong to a conversation, as Authenticated user.

  @api
  Scenario: Content successfully created.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    Given I have the payload:
    """
    {
    "recipients": "[1,2]",
    "text": "Sample text"
    }
    """
    When I request "POST /api/v1/cs-pm/conversations"
    Then The REST API returns a 201 response

 @api
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
     message_type
     conversation_id
     sender
     sent
     avatars
     updated
     text
     weight
     edits
     """

 @api
 Scenario: Attempt to GET messages from an empty conversation.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations/1/messages"
    Then The REST API returns a 204 response

 @api
 Scenario: Attempt to get messages from a conversation that doesn't exist.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations/99999/messages"
    Then The REST API returns a 404 response
