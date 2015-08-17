Feature: GET a message and Update the message text, as Authenticated user.

 Scenario: The user successfully found the message.
   When I request "GET  /cs-pm-api/v1/messages/1"
   Then The REST API returns a 200 response
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

 Scenario: Message wasn't found.
   When I request "GET  /cs-pm-api/v1/messages/999999"
   Then The REST API returns a 404 response
   
 Scenario: The user successfully updated the message text.
   Given I have the payload:
    """
    {
     "text": "Sample text"
    }
    """
   When I request "PUT  /cs-pm-api/v1/messages/1"
   Then The REST API returns a 200 response
   
 Scenario: Invalid request.
   When I request "PUT  /cs-pm-api/v1/messages/1"
   Then The REST API returns a 400 response
   
 Scenario: Looking into the message which doesn't exist
   When I request "PUT  /cs-pm-api/v1/messages/99999"
   Then The REST API returns a 404 response
