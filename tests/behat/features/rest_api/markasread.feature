Feature: Mark a conversation as read, no unread messages within, as Authenticated user.

 Scenario: To verify the conversation has been marked as read and then the user should get 200 HTTP response
   #When I request "PUT  /cs-pm-api/v1/conversations/{conversation_id}/mark-as-read"
   When I request "PUT  /cs-pm-api/v1/conversations/1/mark-as-read"
   Then I should get a 200 HTTP response
   
 Scenario: To verify the Content not found and then the user should get 404 HTTP response
   #When I request "PUT  /cs-pm-api/v1/conversations/{conversation_id}/mark-as-read"
   When I request "PUT  /cs-pm-api/v1/conversations/54/mark-as-read"
   Then I should get a 404 HTTP response
