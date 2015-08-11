Feature: GET a message and Update the message text, as Authenticated user.

 Scenario: Verify The message has been found with proper message ID.
   #When I request "GET  /cs-pm-api/v1/messages/{message_id}"
   When I request "GET  /cs-pm-api/v1/messages/12"
   Then I should get a 200 HTTP response
   And scope into the "data" property
   And the properties exist:
    """
    type
    id
    message_type
    conversation_id
    sender
    sent
    updated
    text
    weight
    edits
    """

 Scenario: Verify The message not found response when user enter wrong message ID.
   #When I request "GET  /cs-pm-api/v1/messages/{message_id}"
   When I request "GET  /cs-pm-api/v1/messages/123"
   Then I should get a 404 HTTP response
   
 Scenario: Verify The Authenticated user can update the message text.
   #When I request "PUT  /cs-pm-api/v1/messages/{message_id}"
   When I request "PUT  /cs-pm-api/v1/messages/12"
   Then I should get a 200 HTTP response
   And scope into the "data" property
   And the properties exist:
    """
    type
    id
    message_type
    conversation_id
    sender
    sent
    updated
    text
    weight
    edits
    """
 Scenario: Verify The Authenticated user getting invalid response when update text with Empty strings..
   #When I request "PUT  /cs-pm-api/v1/messages/{message_id}"
   When I request "PUT  /cs-pm-api/v1/messages/1231"
   Then I should get a 400 HTTP response
    
 Scenario: Verify The message not found response when user enter wrong message ID.
   #When I request "PUT  /cs-pm-api/v1/messages/{message_id}"
   When I request "PUT  /cs-pm-api/v1/messages/123"
   Then I should get a 404 HTTP response
