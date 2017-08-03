<?php

namespace Core\MessageBundle\Controller;

use FOS\RestBundle\Controller\FOSRestController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializationContext;

class ApiMessageController extends FOSRestController {

    /**
     * Posts a message by the current user into the conversation with the user whose
     * id equals request's userId parameter. Returns the message and the conversation.
     * 
     * @param Request $request
     * @return json
     * @throws EntityNotFoundException
     * @throws EntityValidationException
     */
    public function postMessageAction(Request $request) {
        $reciever = $this->get('core_user.user_manager')->getUserOr404($request->request->get('userId'));

        $message = $this->get('core_message.message_manager')->createMessageForUsers($this->getUser(), $reciever, $request->request->get('message'));
        $result = array(
            'message' => $message,
            'conversation' => $message->getConversation()
        );
        
        $view = $this->view($result, Response::HTTP_OK);
        $serializationContext = SerializationContext::create()->setGroups(array('Default'));
        $view->setSerializationContext($serializationContext);    

        return $this->handleView($view);
    }

}
