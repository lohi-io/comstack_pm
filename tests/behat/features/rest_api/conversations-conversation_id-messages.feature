Feature: GET messages that belong to a conversation, as Authenticated user.

 Scenario: The messages in the conversation successfully displayed.
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

 Scenario: When there in no messsages in the conversation, the user should get No content response.
   When I request "GET /api/v1/cs-pm/conversations/1/messages"
   Then The REST API returns a 204 response

 Scenario: Attempt to get the message from the conversation which doesn't exist.
   When I request "GET /api/v1/cs-pm/conversations/9999/messages"
   Then The REST API returns a 404 response
