<?php
namespace sngrl\PhpFirebaseCloudMessaging\Tests;

use sngrl\PhpFirebaseCloudMessaging\Notification;

class NotificationTest extends PhpFirebaseCloudMessagingTestCase
{
    private $fixture;

    protected function setUp()
    {
        parent::setUp();
        $this->fixture = new Notification('foo', 'bar');
    }

    public function testJsonSerializeWithMinimalConfigurations()
    {
        $this->assertEquals(array('title' => 'foo', 'body' =>'bar'), $this->fixture->jsonSerialize());
    }

    public function testJsonSerializeWithBadge()
    {
        $this->fixture->setBadge(1);
        $this->assertEquals(array('title' => 'foo', 'body' =>'bar', 'badge' => 1), $this->fixture->jsonSerialize());
    }

    public function testJsonSerializeWithIcon()
    {
        $this->fixture->setIcon('name');
        $this->assertEquals(array('title' => 'foo', 'body' =>'bar', 'icon' => 'name'), $this->fixture->jsonSerialize());
    }

    public function testJsonSerializeWithContentAvailable()
    {
        $this->fixture->setContentAvailable(true);
        $this->assertEquals(array('title' => 'foo', 'body' =>'bar', 'content_available' => true), $this->fixture->jsonSerialize());
    }

    public function testJsonSerializeWithColor()
    {
        $this->fixture->setColor('#FF00FF');
        $this->assertEquals(array('title' => 'foo', 'body' =>'bar', 'color' => '#FF00FF'), $this->fixture->jsonSerialize());
    }
}
