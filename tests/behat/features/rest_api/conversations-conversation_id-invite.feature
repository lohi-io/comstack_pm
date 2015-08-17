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
   
 Scenario: Unable to invite to the conversation with invalid ids.
   When I request "POST /cs-pm-api/v1/conversations/1/invite"
   Then The REST API returns a 400 response
   
 Scenario: Uable to invite to the non existing conversation.
   Given I have the payload:
   """
   {
     "ids": [1, 2, 3],
   }
   """
   When I request "POST /cs-pm-api/v1/conversations/52/invite"
   Then The REST API returns a 404 response
