Feature: Invite a user to the conversation, as Authenticated user.

 Scenario: The user should be able to invite valid user to the existing conversation.
   Given I have the payload:
   """
   {
     "ids": [1, 2, 3],
   }
   """
   When I request "POST /cs-pm-api/v1/conversations/1/invite"
   Then The REST API returns a 200 response
   
 Scenario: Invalid request to invite users to a conversation without sending ids.
   When I request "POST /cs-pm-api/v1/conversations/1/invite"
   Then The REST API returns a 400 response
   
 Scenario: Invite users to a conversation which doesn't exist.
   Given I have the payload:
   """
   {
     "ids": [1, 2, 3],
   }
   """
   When I request "POST /cs-pm-api/v1/conversations/99999/invite"
   Then The REST API returns a 404 response
