Feature: Report a conversation as Authenticated user. 

 Scenario: To verify the response code for the Content created.
   #When I request "POST  /cs-pm-api/v1/conversations/{conversation_id}/report"
   When I request "POST  /cs-pm-api/v1/conversations/1/report"
   Then I should get a 200 HTTP response
   And scope into the "data" property
   And the properties exist:
    """
    reasons
    other_reason
    posts
    """
 Scenario: Validation issue or otherwise, see issue text.
   #When I request "POST /cs-pm-api/v1/conversations/{conversation_id}/report"
   When I request "POST /cs-pm-api/v1/conversations/test/report"
   Then I should get a 400 HTTP response
   
Scenario: Conversation not found.
   #When I request "POST /cs-pm-api/v1/conversations/{conversation_id}/report"
   When I request "POST /cs-pm-api/v1/conversations/524/report"
   Then I should get a 404 HTTP response
