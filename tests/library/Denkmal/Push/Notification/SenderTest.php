<?php

class Denkmal_Push_Notification_SenderTest extends CMTest_TestCase {

    protected function tearDown() {
        CMTest_TH::clearEnv();
    }

    public function testSendNotifications() {
        $message = new Denkmal_Push_Notification_Message();
        $message->setExpires(new DateTime('2015-01-01'));
        $message->setData(['foo' => 'bar']);

        /** @var Denkmal_Push_Subscription[] $subscriptionList */
        $subscriptionList = [
            $subscriptionFoo1 = Denkmal_Push_Subscription::create('foo1', 'foo'),
            $subscriptionBar1 = Denkmal_Push_Subscription::create('bar1', 'bar'),
            $subscriptionFoo2 = Denkmal_Push_Subscription::create('foo2', 'foo'),
            $subscriptionBar2 = Denkmal_Push_Subscription::create('bar2', 'bar'),
        ];
        $subscriptionListFoo = [$subscriptionFoo1, $subscriptionFoo2];
        $subscriptionListBar = [$subscriptionBar1, $subscriptionBar2];

        $providerClass = $this->mockClass('Denkmal_Push_Notification_Provider_Abstract');
        $providerFoo = $providerClass->newInstance([$this->getServiceManager()]);
        $sendNotificationFooMethod = $providerFoo->mockMethod('sendNotifications')
            ->set(function (array $subscriptionList, Denkmal_Push_Notification_Message $message) use ($subscriptionListFoo) {
                $this->assertEquals($subscriptionListFoo, $subscriptionList);
            });

        $providerBar = $providerClass->newInstance([$this->getServiceManager()]);
        $sendNotificationBarMethod = $providerBar->mockMethod('sendNotifications')
            ->set(function (array $subscriptionList, Denkmal_Push_Notification_Message $message) use ($subscriptionListBar) {
                $this->assertEquals($subscriptionListBar, $subscriptionList);
            });

        $senderClass = $this->mockClass('Denkmal_Push_Notification_Sender');
        $senderClass->mockMethod('_getProvider')
            ->at(0, function ($endpoint) use ($providerFoo) {
                $this->assertSame('foo', $endpoint);
                return $providerFoo;
            })
            ->at(1, function ($endpoint) use ($providerBar) {
                $this->assertSame('bar', $endpoint);
                return $providerBar;
            });

        $clientConfig = [];
        $sender = $senderClass->newInstance([$clientConfig]);
        /** @var Denkmal_Push_Notification_Sender $sender */
        $sender->setServiceManager($this->getServiceManager());
        $sender->sendNotifications($subscriptionList, $message);

        $this->assertSame(1, $sendNotificationFooMethod->getCallCount());
        $this->assertSame(1, $sendNotificationBarMethod->getCallCount());

        foreach ($subscriptionList as $subscription) {
            $this->assertCount(1, $subscription->getMessageList());
            /** @var Denkmal_Push_Notification_Message $messageFromSubscription */
            $messageFromSubscription = $subscription->getMessageList()->getItem(0);
            $this->assertEquals($message->getExpires(), $messageFromSubscription->getExpires());
            $this->assertEquals($message->getData(), $messageFromSubscription->getData());
        }
    }
}
