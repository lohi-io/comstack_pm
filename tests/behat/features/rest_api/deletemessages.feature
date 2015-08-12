Feature: Delete messages that belong to a conversation, as Authenticated user.

 Scenario: Messages deleted.
   #When I request "POST /cs-pm-api/v1/conversations/{conversation_id}/messages/delete"
   When I request "POST /cs-pm-api/v1/conversations/1/messages/delete"
   Then I should get a 200 HTTP response
   And scope into the "data" property
   #An array of message ids to be deleted from the conversation.
   And the "ids" property exists
   
Scenario: Validation issue, most likely that ids array is missing.
   #When I request "POST /cs-pm-api/v1/conversations/{conversation_id}/messages/delete"
   When I request "POST /cs-pm-api/v1/conversations/test/messages/delete"
   Then I should get a 400 HTTP response
   
   
Scenario: Content not found.
   #When I request "POST /cs-pm-api/v1/conversations/{conversation_id}/messages/delete"
   When I request "POST /cs-pm-api/v1/conversations/55/messages/delete"
   Then I should get a 404 HTTP response
