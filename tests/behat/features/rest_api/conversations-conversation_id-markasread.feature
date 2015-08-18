Feature: Mark a conversation as read, no unread messages within, as Authenticated user.

 Scenario: The conversation successfully marked as read.
   When I request "PUT  /api/v1/cs-pm/conversations/1/mark-as-read"
   Then The REST API returns a 200 response

 Scenario: Attempt to mark a conversation which doesn't exist.
   When I request "PUT  /api/v1/cs-pm/conversations/54/mark-as-read"
   Then The REST API returns a 404 response
