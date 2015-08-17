Feature: Removing the valid user from the conversation, as Authenticated user.

 Scenario: The user was removed successfully from the conversation.
   When I request "PUT  /cs-pm-api/v1/conversations/1/leave"
   Then The REST API returns a 200 response
   
 Scenario: Attempt to remove user from a conversation which doesn't exist.
   When I request "PUT  /cs-pm-api/v1/conversations/99999/leave"
   Then The REST API returns a 404 response
