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
   
Scenario: Validation issue, most likely that ids array is missing.
   When I request "POST /cs-pm-api/v1/conversations/1/messages/delete"
   Then The REST API returns a 400 response
   
Scenario: Content not found.
   #When I request "POST /cs-pm-api/v1/conversations/{conversation_id}/messages/delete"
   When I request "POST /cs-pm-api/v1/conversations/55/messages/delete"
   Then I should get a 404 HTTP response
