Feature: Delete messages that belong to a conversation, as Authenticated user.

 Scenario: Messages successfully deleted.
   Given I have the payload:
   """
   {
     "ids": [1, 2, 3],
   }
   """
   When I request "POST /cs-pm-api/v1/conversations/1/messages/delete"
   Then The REST API returns a 200 response
   
Scenario: Attempt to delete messages without sending any ids, an invalid request.
   When I request "POST /cs-pm-api/v1/conversations/1/messages/delete"
   Then The REST API returns a 400 response
   
Scenario: Attempt to delete messages from a conversation which doesn't exist.
   Given I have the payload:
   """
   {
     "ids": [1, 2, 3],
   }
   """
   When I request "POST /cs-pm-api/v1/conversations/99999/messages/delete"
   Then The REST API returns a 404 response
