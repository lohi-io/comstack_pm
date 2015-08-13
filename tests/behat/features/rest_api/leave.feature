Feature: Remove the authenticated user from the specified conversation, technically remove the user from the participants property array, as Authenticated user.

 Scenario: The user was removed from the conversation.
   #When I request "PUT  /cs-pm-api/v1/conversations/{conversation_id}/leave"
   When I request "PUT  /cs-pm-api/v1/conversations/1/leave"
   Then I should get a 200 HTTP response
   
 Scenario: Conversation not found.
   #When I request "PUT  /cs-pm-api/v1/conversations/{conversation_id}/leave"
   When I request "PUT  /cs-pm-api/v1/conversations/54/leave"
   Then I should get a 404 HTTP response
