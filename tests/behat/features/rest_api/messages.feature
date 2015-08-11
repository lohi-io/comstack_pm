Feature: GET messages that belong to a conversation., as Authenticated user.

 Scenario: To verify the "200" response.
   #When I request "GET  /cs-pm-api/v1/conversations/{conversation_id}/messages"
   When I request "GET  /cs-pm-api/v1/conversations/1/messages"
   Then I should get a 200 HTTP response
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
    
 Scenario: No content (no messages in conversation). To verify the "204" response.
   #When I request "GET  /cs-pm-api/v1/conversations/{conversation_id}/messages"
   When I request "GET  /cs-pm-api/v1/conversations/test/messages"
   Then I should get a 204 HTTP response
   
 Scenario: Conversation not found.. To verify the "404" response.
   #When I request "GET  /cs-pm-api/v1/conversations/{conversation_id}/messages"
   When I request "GET  /cs-pm-api/v1/conversations/00/messages"
   Then I should get a 404 HTTP response
