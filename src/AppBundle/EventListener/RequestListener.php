<?php

namespace AppBundle\EventListener;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;

class RequestListener
{
    protected $token;

    protected $router;

    protected $authChecker;

    public function __construct(TokenStorage $token, AuthorizationChecker $authChecker, Router $router)
    {
        $this->token = $token;
        $this->authChecker = $authChecker;
        $this->router = $router;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            // don't do anything if it's not the master request
            return;
        }
        $token = $this->token->getToken();
        // if is logged
        if (!$token || !$this->authChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return;
        }
        $request = $event->getRequest();
        // no infinite redirect !
        if ($request->getPathInfo() === '/email') {
            return;
        }
        $user = $token->getUser();
        // if not email, redirect to form
        if ($user->getEmail() === null) {
            $url = $this->router->generate('email_form');
            $event->setResponse(new RedirectResponse($url));
        }
    }
}
