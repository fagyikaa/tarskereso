<?php

namespace Core\CommonBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Core\UserBundle\Events\UserBundleEvents;
use Core\UserBundle\Events\UserNewFriendshipRequest;
use Core\MessageBundle\Events\MessageBundleEvents;
use Core\MessageBundle\Events\NewMessageEvent;
use Facile\CrossbarHTTPPublisherBundle\Publisher\Publisher;
use Facile\CrossbarHTTPPublisherBundle\Exception\PublishRequestException;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;

class WebsocketPublisherSubscriber implements EventSubscriberInterface {

    const TOPIC_NEW_MESSAGE = 'message/new_message_backend';
    const TOPIC_NEW_FRIEND_REQUEST = 'user/new_friendship_request_backend';

    protected $websocketPublisher;
    protected $serializer;

    public function __construct(Publisher $websocketPublisher, SerializerInterface $serializer) {
        $this->websocketPublisher = $websocketPublisher;
        $this->serializer = $serializer;
    }

    public static function getSubscribedEvents() {
        return array(
            MessageBundleEvents::MESSAGE_NEW_MESSAGE => 'onNewMessage',
            UserBundleEvents::USER_NEW_FRIENDSHIP_REQUEST => 'onNewFriendshipRequest',
        );
    }

    /**
     * If a user sends a new message to an other user then pubishes TOPIC_NEW_MESSAGE topic 
     * with the new Message.
     * 
     * @param UserBundleEvent $event
     */
    public function onNewMessage(NewMessageEvent $event) {
        $message = $event->getMessage();

        $serializationContext = SerializationContext::create()->setGroups(array('websocket'));
        $data = $this->serializer->serialize($message, 'json', $serializationContext);

        $this->publish(self::TOPIC_NEW_MESSAGE, array($data));
    }

    /**
     * If a user sends a new friendship request to an other user then pubishes 
     * TOPIC_NEW_MESSAGE topic with the new Friendship.
     * 
     * @param UserBundleEvent $event
     */
    public function onNewFriendshipRequest(UserNewFriendshipRequest $event) {
        $friendship = $event->getFriendship();

        $serializationContext = SerializationContext::create()->setGroups(array('websocket'));
        $data = $this->serializer->serialize($friendship, 'json', $serializationContext);

        $this->publish(self::TOPIC_NEW_FRIEND_REQUEST, array($data));
    }

    /**
     * Publishes to websocket the $topic with args: $args.
     * 
     * @param string $topic
     * @param array $args
     */
    private function publish($topic, array $args = null) {
        try {
            $this->websocketPublisher->publish($topic, $args);
        } catch (PublishRequestException $exception) {
            //Couldn't connect to crossbar
            return;
        }
    }

}
