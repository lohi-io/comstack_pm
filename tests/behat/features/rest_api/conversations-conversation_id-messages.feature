Feature: GET messages that belong to a conversation, as Authenticated user.

 @api
 Scenario: The messages in the conversation successfully displayed.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations/1/messages"
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
     avatars
     updated
     text
     weight
     edits
     """

 @api
 Scenario: When there in no messsages in the conversation, the user should get No content response.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations/1/messages"
    Then The REST API returns a 204 response

 @api
 Scenario: Attempt to get the message from the conversation which doesn't exist.
    Given I am logged in as a user with the authenticated role
    And I have an access token
    When I request "GET /api/v1/cs-pm/conversations/9999/messages"
    Then The REST API returns a 404 response
