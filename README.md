# PHP Firebase Cloud Messaging
PHP API for Firebase Cloud Messaging from Google.

Currently this app server library only supports sending Messages/Notifications via HTTP.

See original Firebase docs: https://firebase.google.com/docs/

#Setup
Install via Composer:
```
composer require sngrl/php-firebase-cloud-messaging
```

Or add this to your composer.json and run "composer update":

```
"require": {
    "sngrl/php-firebase-cloud-messaging": "dev-master"
}
```

#Send message to Device
```
use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;
use sngrl\PhpFirebaseCloudMessaging\Notification;

$server_key = '_YOUR_SERVER_KEY_';
$client = new Client();
$client->setApiKey($server_key);
$client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

$message = new Message();
$message->setPriority('high');
$message->addRecipient(new Device('_YOUR_DEVICE_TOKEN_'));
$message
    ->setNotification(new Notification('some title', 'some body'))
    ->setData(['key' => 'value'])
;

$response = $client->send($message);
var_dump($response->getStatusCode());
var_dump($response->getBody()->getContents());
```

#Send message to multiple Devices

```
...
$message = new Message();
$message->setPriority('high');
$message->addRecipient(new Device('_YOUR_DEVICE_TOKEN_'));
$message->addRecipient(new Device('_YOUR_DEVICE_TOKEN_2_'));
$message->addRecipient(new Device('_YOUR_DEVICE_TOKEN_3_'));
$message
    ->setNotification(new Notification('some title', 'some body'))
    ->setData(['key' => 'value'])
;
...
```
#Send message to Topic

```
use sngrl\PhpFirebaseCloudMessaging\Client;
use sngrl\PhpFirebaseCloudMessaging\Message;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Topic;
use sngrl\PhpFirebaseCloudMessaging\Notification;

$server_key = '_YOUR_SERVER_KEY_';
$client = new Client();
$client->setApiKey($server_key);
$client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

$message = new Message();
$message->setPriority('high');
$message->addRecipient(new Topic('_YOUR_TOPIC_'));
$message
    ->setNotification(new Notification('some title', 'some body'))
    ->setData(['key' => 'value'])
;

$response = $client->send($message);
var_dump($response->getStatusCode());
var_dump($response->getBody()->getContents());
```

#Send message to multiple Topics

See Firebase documentation for sending to [combinations of multiple topics](https://firebase.google.com/docs/cloud-messaging/topic-messaging#sending_topic_messages_from_the_server).

```
...
$message = new Message();
$message->setPriority('high');
$message->addRecipient(new Topic('_YOUR_TOPIC_'));
$message->addRecipient(new Topic('_YOUR_TOPIC_2_'));
$message->addRecipient(new Topic('_YOUR_TOPIC_3_'));
$message
    ->setNotification(new Notification('some title', 'some body'))
    ->setData(['key' => 'value'])
    // Will send to devices subscribed to topic 1 AND topic 2 or 3
    ->setCondition('%s && (%s || %s)')
;
...
```

#Subscribe user to the topic
```
use sngrl\PhpFirebaseCloudMessaging\Client;

$server_key = '_YOUR_SERVER_KEY_';
$client = new Client();
$client->setApiKey($server_key);
$client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

$response = $client->addTopicSubscription('_SOME_TOPIC_ID_', ['_FIRST_TOKEN_', '_SECOND_TOKEN_']);
var_dump($response->getStatusCode());
var_dump($response->getBody()->getContents());
```

#Remove user subscription to the topic
```
use sngrl\PhpFirebaseCloudMessaging\Client;

$server_key = '_YOUR_SERVER_KEY_';
$client = new Client();
$client->setApiKey($server_key);
$client->injectGuzzleHttpClient(new \GuzzleHttp\Client());

$response = $client->removeTopicSubscription('_SOME_TOPIC_ID_', ['_FIRST_TOKEN_', '_SECOND_TOKEN_']);
var_dump($response->getStatusCode());
var_dump($response->getBody()->getContents());
```
