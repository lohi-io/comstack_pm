Feature: Invite a user to the conversation, as Authenticated user.

 Scenario: The user has been invited and user gets "200" HTTP response
   #When I request "POST /cs-pm-api/v1/conversations/{conversation_id}/invite"
   When I request "POST /cs-pm-api/v1/conversations/1/invite"
   Then I should get a 200 HTTP response
   And scope into the "data" property
   #An array of user ids to be invited to the conversation.
   And the "ids" property exists
   
 Scenario: If the ids were missing or some user ids were invalid and then user gets "400" HTTP response
   #When I request "POST /cs-pm-api/v1/conversations/{conversation_id}/invite"
   When I request "POST /cs-pm-api/v1/conversations/test/invite"
   Then I should get a 400 HTTP response
   
 Scenario: If the conversation not found, and then the user should get "404" HTTP response
   #When I request "POST /cs-pm-api/v1/conversations/{conversation_id}/invite"
   When I request "POST /cs-pm-api/v1/conversations/52/invite"
   Then I should get a 404 HTTP response
