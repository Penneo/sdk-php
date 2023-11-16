Feature: Customers
  As an SDK user
  I need functionality to read customer data

Background:
  Given customer exist:
  | id | name       | address  | zip  | city      | active | vatin    | status |
  |  1 | Customer 1 | Street 1 | 1234 | Test city |      1 | 12345678 |      1 |
  And customer 1 has email signature:
  | id | html                               |
  |  7 | <p>Best regards</p><p>Acme Inc</p> |
  And customer 1 has branding:
  | id | backgroundColor | highlightColor | textColor | siteUrl         | imageId |
  |  4 | #AAAAAA         | #BBBBBB        | #CCCCCC   | http://acme.com |      10 |
  And image exist:
  | id | url                        |
  | 10 | http://penneo.com/logo.png |

Scenario: Read customer
  When I retrieve customer 1
  Then a GET request should be sent to "/customers/1"
   And entity property "name" should contain "Customer 1"
   And entity property "address" should contain "Street 1"
   And entity property "zip" should contain "1234"
   And entity property "city" should contain "Test city"
   And entity property "active" should contain 1
   And entity property "vatin" should contain "12345678"
   And entity property "status" should contain "paying"

Scenario: Read customer branding
  Given I retrieve customer 1
  When I retrieve customer 1 branding
  Then a GET request should be sent to "/customers/1/branding"
   And entity property "backgroundColor" should contain "#AAAAAA"
   And entity property "highlightColor" should contain "#BBBBBB"
   And entity property "textColor" should contain "#CCCCCC"
   And entity property "siteUrl" should contain "http://acme.com"

Scenario: Read customer branding logo
  Given I retrieve customer 1
  And I retrieve customer 1 branding
  When I retrieve branding logo
  Then a GET request should be sent to "/customers/1/images/10"
   And branding logo url should be "http://penneo.com/logo.png"

Scenario: Read customer email signature
  Given I retrieve customer 1
  When I retrieve customer 1 email signature
  Then a GET request should be sent to "/customers/1/email-signature"
   And entity property "html" should contain "<p>Best regards</p><p>Acme Inc</p>"
