Feature: Mark a conversation as unread, as Authenticated user.

 Scenario: The conversation successfully marked as unread.
   When I request "PUT  /cs-pm-api/v1/conversations/1/mark-as-unread"
   Then The REST API returns a 200 response
   
 Scenario: Attempt to mark a conversation which doesn't exist.
   When I request "PUT  /cs-pm-api/v1/conversations/54/mark-as-unread"
   Then The REST API returns a 404 response