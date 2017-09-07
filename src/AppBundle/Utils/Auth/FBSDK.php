<?php

namespace AppBundle\Utils\Auth;

use Facebook\Facebook;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\GraphNodes\GraphUser;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class FBSDK
{
    /**
     * FB app id.
     *
     * @var string
     */
    private $app_id;

    /**
     * FB app secret.
     *
     * @var string
     */
    private $app_secret;

    /**
     * FB utility.
     *
     * @var Facebook
     */
    private $fb;

    /**
     * Helper Redirect.
     *
     * @var object
     */
    public $redirectLoginHelper;

    /**
     * access token FB.
     *
     * @var string
     */
    private $accessToken;

    /**
     * @param string $app_id
     * @param string $app_secret
     */
    public function __construct(string $app_id, string $app_secret)
    {
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
    }

    /**
     * return Facebook class.
     *
     * @return Facebook
     */
    public function create(): Facebook
    {
        $this->fb = new Facebook([
          'app_id' => $this->app_id,
          'app_secret' => $this->app_secret,
          'default_graph_version' => 'v2.2',
        ]);
        // init redirect helper
        $this->getRedirectLoginHelper();
        return $this->fb;
    }

    /**
     * init redirect helper.
     */
    public function getRedirectLoginHelper(): void
    {
        $this->redirectLoginHelper = $this->fb->getRedirectLoginHelper();
    }

    /**
     * get Access Token.
     *
     * @return any
     */
    public function getAccessToken(): void
    {
        try {
            $this->accessToken = $this->redirectLoginHelper->getAccessToken();
        } catch (FacebookResponseException $e) {
            throw new AuthenticationException('Graph returned an error: '.$e->getMessage());
        } catch (FacebookSDKException $e) {
            throw new AuthenticationException('Facebook SDK returned an error: '.$e->getMessage());
        }
    }

    public function query(string $query = '/me?fields=id,name,email'): GraphUser
    {
        try {
            $response = $this->fb->get($query, $this->accessToken);
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            throw new AuthenticationException('Graph returned an error: '.$e->getMessage());
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            throw new AuthenticationException('Facebook SDK returned an error: '.$e->getMessage());
        }
        return $response->getGraphUser();
    }
}
