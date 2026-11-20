# "Connected Services - Notify" service

Here in the BC Government, we have recently launched our own Notify service. The idea is to make it easy for all government apps to send emails or text messages.

This is a module for use with the [Drupal](https://www.drupal.org) content management system. You can use it on any Drupal site, though we like using it in conjunction with our ['Base build' project](https://github.com/bcgov-c/fin-basebuild), so that it can run on Openshift.

This module is open to the public, but the Notify API is not -- you'll need an API key to use it, and will need a BC gov employees' _IDIR_ account to get that.

The Notify API docs are here:
* https://notify.digital.gov.bc.ca/api/docs

So far, we only use `/api/v1/notifysimple`, aka "Send a notification".

## How to use it:

* Download and enable the module.
* Follow the CSTAR and Notify instructions to get set up with your account and get the api key.
* Configure the module at https://YOUR_WEBSITE.com/admin/config/system/cs-notify
* If you've injected our service into your code, then you can use this. We used HTML in this example:

```
    $this->notificationClient->sendEmailQueued(
      ['bob@test.com'], 
      'The subject of your HTML email', 
      "<h2>Important heading!</h2>
         <p>This is the body of your email.</p>
      ",
      'html'
    );
```
Otherwise, like this. We've used Markdown in this example
```
\Drupal::service('cs_notify.client')->sendEmailQueued(
      ['robert@test.com', 'sally@test.com', 'fred@test.com'], 
      'The subject of your Markdown email', 
"
# Important heading!
This is the body of your email.
",
      'markdown'
    );
```
## Under the hood

### How a send works
1. Your code will use the `sendEmailQueued()` method, to _queue_ an email for sending.
1. The queue is processed "soon" (see below), and the email is handed off to the Notify API for sending. 
    *   It _still_ hasn't been actually sent yet. We add an entry to a _second_ queue, to check up on it later.
1. When the second queue is processed, we check back with the Notfy API, to say "has my message been delivered yet?". 
    * If it was delivered, we log that. 
    * If the delivery failed, we log that. 
    * If it hasn't been delivered yet, we leave it in the queue to check again, later.

### What you have to do

Drupal adds things to 'queues', to process later. "Later" only comes when you do the 'run queue' process. 
* This happens automatically each time that Drupal's 'cron' is run. But if cron is only running infrequently, then your emails will also only be delivered infrequently.
* If you want to run your own process to do this, you can do it by calling these two commands:
  * `drush queue:run cs_notify_email_to_hand_off`
  * `drush queue:run cs_notify_email_status_check`

  ... the more frequently you run those, the more frequently emails will get sent out.
  

## To do
* Add support for CC and BCC.
* Add support for templates
* Add support for SMS

## Contact

This module was developed by Dan Howard, at Connected Services BC, initially 
for use with the [Ministry of Finance Data Catalogue](https://cat.data.fin.gov.bc.ca/). I very much welcome any 
feedback -- feature requests, bug fixes, or conversations about how this could be better.