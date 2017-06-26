<?php
namespace sngrl\PhpFirebaseCloudMessaging;

use sngrl\PhpFirebaseCloudMessaging\Recipient\Recipient;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Topic;
use sngrl\PhpFirebaseCloudMessaging\Recipient\Device;

/**
 * @author sngrl
 */
class Message implements \JsonSerializable
{
    /**
     * Maximum topics and devices: https://firebase.google.com/docs/cloud-messaging/http-server-ref#send-downstream
     */
    const MAX_TOPICS = 3;
    const MAX_DEVICES = 1000;
    
    private $notification;
    private $collapseKey;    
    private $priority;
    private $data;
    private $recipients = [];
    private $recipientType;    
    private $jsonData;
    private $condition;


    public function __construct() {
        $this->jsonData = [];
    }

    /**
     * where should the message go
     *
     * @param Recipient $recipient
     *
     * @return \sngrl\PhpFirebaseCloudMessaging\Message
     */
    public function addRecipient(Recipient $recipient)
    {
        $this->recipients[] = $recipient;

        if (!isset($this->recipientType)) {
            $this->recipientType = get_class($recipient);
        }
        if ($this->recipientType !== get_class($recipient)) {
            throw new \InvalidArgumentException('mixed recepient types are not supported by FCM');
        }

        return $this;
    }

    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;
        return $this;
    }

    public function setCollapseKey($collapseKey)
    {
        $this->collapseKey = $collapseKey;
        return $this;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }
            
    /**
     * Specify a condition pattern when sending to combinations of topics
     * https://firebase.google.com/docs/cloud-messaging/topic-messaging#sending_topic_messages_from_the_server
     *
     * Examples:
     * "%s && %s" > Send to devices subscribed to topic 1 and topic 2
     * "%s && (%s || %s)" > Send to devices subscribed to topic 1 and topic 2 or 3
     *
     * @param string $condition
     * @return $this
     */
    public function setCondition($condition) {
        $this->condition = $condition;
        return $this;
    }

    /**
     * Set root message data via key
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function setJsonKey($key, $value) {
        $this->jsonData[$key] = $value;
        return $this;
    }

    /**
     * Unset root message data via key
     *
     * @param string $key
     * @return $this
     */
    public function unsetJsonKey($key) {
        unset($this->jsonData[$key]);
        return $this;
    }

    /**
     * Get root message data via key
     *
     * @param string $key
     * @return mixed
     */
    public function getJsonKey($key) {
        return $this->jsonData[$key];
    }

    /**
     * Get root message data
     *
     * @return array
     */
    public function getJsonData() {
        return $this->jsonData;
    }

    /**
     * Set root message data
     *
     * @param array $array
     * @return $this
     */
    public function setJsonData($array) {
        $this->jsonData = $array;
        return $this;
    }


    public function setDelayWhileIdle($value)
    {
        $this->setJsonKey('delay_while_idle', (bool)$value);
        return $this;
    }

    public function setTimeToLive($value)
    {
        $this->setJsonKey('time_to_live', (int)$value);
        return $this;
    }

    public function jsonSerialize()
    {
        $jsonData = $this->jsonData;

        if (empty($this->recipients)) {
            throw new \UnexpectedValueException('Message must have at least one recipient');
        }
        
        if (count($this->recipients) == 1) {
            $jsonData['to'] = $this->createTarget();    
        } elseif ($this->recipientType == Device::class) {
            $jsonData['registration_ids'] = $this->createTarget();
        } else {
            $jsonData['condition'] = $this->createTarget();
        }       
        
        if ($this->collapseKey) {
            $jsonData['collapse_key'] = $this->collapseKey;
        }
        if ($this->data) {
            $jsonData['data'] = $this->data;
        }
        if ($this->priority) {
            $jsonData['priority'] = $this->priority;
        }
        if ($this->notification) {
            $jsonData['notification'] = $this->notification;
        }

        return $jsonData;
    }

    private function createTarget()
    {
        $recipientCount = count($this->recipients);
        
        switch ($this->recipientType) {
                
            case Topic::class:
                
                if ($recipientCount == 1) {
                    return sprintf('/topics/%s', current($this->recipients)->getName());    
                    
                } else if ($recipientCount > self::MAX_TOPICS) {
                    throw new \OutOfRangeException(sprintf('Message topic limit exceeded. Firebase supports a maximum of %u topics.', self::MAX_TOPICS));
                    
                } else if (!$this->condition) {
                    throw new \InvalidArgumentException('Missing message condition. You must specify a condition pattern when sending to combinations of topics.');
                    
                } else if ($recipientCount != substr_count($this->condition, '%s')) {                    
                    throw new \UnexpectedValueException('The number of message topics must match the number of occurrences of "%s" in the condition pattern.');
                    
                } else {
                    $names = [];
                    foreach ($this->recipients as $recipient) {
                        $names[] = vsprintf("'%s' in topics", $recipient->getName());
                    }
                    return vsprintf($this->condition, $names);
                }                
                break;

            case Device::class:
                
                if ($recipientCount == 1) { 
                    return current($this->recipients)->getToken();
                    
                } else if ($recipientCount > self::MAX_DEVICES) {
                    throw new \OutOfRangeException(sprintf('Message device limit exceeded. Firebase supports a maximum of %u devices.', self::MAX_DEVICES));
                    
                } else {
                    $ids = [];
                    foreach ($this->recipients as $recipient) {                        
                        $ids[] = $recipient->getToken();
                    }
                    return $ids;
                }
                break;
                
            default:
                break;
        }
        return null;
    }
}