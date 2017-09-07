<?php

namespace AppBundle\Utils\Auth;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Routing\RouterInterface;
use Doctrine\ORM\EntityManager;
use AppBundle\Entity\User;
use Facebook\GraphNodes\GraphUser;

class FBAuthenticator extends AbstractGuardAuthenticator
{
    private $fb_sdk;

    private $router;

    private $entity_manager;

    public function __construct(EntityManager $entity_manager, RouterInterface $router, FBSDK $fb_sdk)
    {
        $this->entity_manager = $entity_manager;
        $this->router = $router;
        $this->fb_sdk = $fb_sdk;
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser(). Returning null will cause this authenticator
     * to be skipped.
     */
    public function getCredentials(Request $request)
    {
        if ($request->getPathInfo() !== '/fb-callback/') {
            return;
        }
        $fb = $this->fb_sdk->create();
        // get token
        try {
            $this->fb_sdk->getAccessToken();
        } catch (AuthenticationException $e) {
            throw $e;
        }

        // get infos user connected on fb
        try {
            $fbuser = $this->fb_sdk->query();
        } catch (AuthenticationException $e) {
            throw $e;
        }

        if ($fbuser->getId() === null) {
            throw new AuthenticationException('Error Processing Authentication', 1);
        }

        // verif if user exist
        $rep = $this->entity_manager->getRepository('AppBundle:User');
        $user = $rep->findOneBy(['fb_id' => $fbuser->getId()]);
        if ($user === null) {
            $user = $this->createUser($fbuser);
        }

        // What you return here will be passed to getUser() as $credentials
        return ['id' => $user->getId()];
    }

    /**
     * create new User.
     *
     * @param array $fbuser
     *
     * @return User
     */
    private function createUser(GraphUser $fbuser): User
    {
        $user = new User();
        $user->setUsername($fbuser->getName());
        $user->setEmail($fbuser->getEmail());
        $user->setFbId($fbuser->getId());
        $user->setRole('ROLE_USER');
        $this->entity_manager->persist($user);
        $this->entity_manager->flush();
        return $user;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        $id = $credentials['id'];
        if (null === $id) {
            return;
        }

        // if a User object, checkCredentials() is called
        return $userProvider->loadUserByUsername($id);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // check credentials - e.g. make sure the password is valid
        // no credential check is needed in this case
        // return true to cause authentication success
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        // success redirect to homepage
        $url = $this->router->generate('fb-access');
        return new RedirectResponse($url);
    }

    /**
     * {@inheritdoc}
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // fail redirect to login
        $url = $this->router->generate('homepage');
        return new RedirectResponse($url);
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $url = $this->router->generate('homepage');
        return new RedirectResponse($url);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsRememberMe()
    {
        return true;
    }
}
