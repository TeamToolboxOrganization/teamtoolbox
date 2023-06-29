<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller;

use App\Entity\MsToken;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Bundle\SecurityBundle\Security;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Unirest;

#[Route('/msgraph')]
class MsGraphController extends AbstractController
{
    #[Route("/signin",name: "msgraph_signin")]
    public function signin(Request $request): Response
    {
        // Initialize the OAuth client
        $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
            'clientId' => $this->getParameter('azure.appId'),
            'clientSecret' => $this->getParameter('azure.appSecret'),
            'redirectUri' => $this->getParameter('azure.redirectUri'),
            'urlAuthorize' => $this->getParameter('azure.authority') . $this->getParameter('azure.authorizeEndpoint'),
            'urlAccessToken' => $this->getParameter('azure.authority') . $this->getParameter('azure.tokenEndpoint'),
            'urlResourceOwnerDetails' => '',
            'scopes' => $this->getParameter('azure.scopes')
        ]);

        $authUrl = $oauthClient->getAuthorizationUrl();

        /*$session = new Session();
        $session->start();

        // set and get session attributes
        $session->set('oauthState', $oauthClient->getState());*/

        // Save client state so we can validate in callback
        $request->getSession()->set('oauthState', $oauthClient->getState());
        //session(['oauthState' => $oauthClient->getState()]);

        // Redirect to AAD signin page
        //return redirect()->away($authUrl);
        return $this->redirect($authUrl);
    }

    #[Route("/callback",name: "msgraph_callback")]
    public function callback(Request $request, RequestStack $requestStack, Security $security, UserRepository $users, ManagerRegistry $managerRegistry): Response
    {
        // Validate state
        //$expectedState = $request->getSession()->get('oauthState');
        $request->getSession()->remove('oauthState');
        //$providedState = $request->get('state');

        /*
            if (!isset($expectedState)) {
                // If there is no expected state in the session,
                // do nothing and redirect to the home page.
                return $this->redirect('/');
            }

            if (!isset($providedState) || $expectedState != $providedState) {
                return $this->redirect('/');
                //->with('error', 'Invalid auth state')
                //->with('errorDetail', 'The provided auth state did not match the expected value');
            }
        */

        // Authorization code should be in the "code" query param
        $authCode = $request->get('code');
        if (isset($authCode)) {
            // Initialize the OAuth client
            $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
                'clientId' => $this->getParameter('azure.appId'),
                'clientSecret' => $this->getParameter('azure.appSecret'),
                'redirectUri' => $this->getParameter('azure.redirectUri'),
                'urlAuthorize' => $this->getParameter('azure.authority') . $this->getParameter('azure.authorizeEndpoint'),
                'urlAccessToken' => $this->getParameter('azure.authority') . $this->getParameter('azure.tokenEndpoint'),
                'urlResourceOwnerDetails' => '',
                'scopes' => $this->getParameter('azure.scopes')
            ]);

            try {
                // Make the token request
                $accessToken = $oauthClient->getAccessToken('authorization_code', [
                    'code' => $authCode
                ]);

                $existingUserInDb = $this->authentifyUser($request, $requestStack, $users, $accessToken, $managerRegistry);

                if(!$existingUserInDb){
                    return $this->redirect('/');
                }

                return $this->redirectToRoute('dashboard_index');

                // TEMPORARY FOR TESTING!
                //return $this->redirect('/');
                /*->with('error', 'Access token received')
                ->with('errorDetail', $accessToken->getToken());*/
            } catch (IdentityProviderException $e) {
                return $this->redirect('/');
                /*->with('error', 'Error requesting access token')
                ->with('errorDetail', $e->getMessage());*/
            }
        }

        return $this->redirect('/');
        /*->with('error', $request->query('error'))
        ->with('errorDetail', $request->query('error_description'));*/
    }



    private function authentifyUser(Request $request, RequestStack $requestStack,UserRepository $users, AccessToken $accessToken, ManagerRegistry $managerRegistry){
        $graph = new Graph();
        $graph->setAccessToken($accessToken->getToken());

        /**
         * @var $userMS Model\User
         */
        $userMS = $graph->createRequest('GET', '/me?$select=displayName,preferredLanguage,mail,mailboxSettings,userPrincipalName,photo')
            ->setReturnType(Model\User::class)
            ->execute();

        $user = $users->findOneBy(['email' => $userMS->getMail()]);

        if(!$user){
            $user = new User();
            $user->setEmail($userMS->getMail());
            $user->setFullName($userMS->getDisplayName());
            $user->setUsername($userMS->getMail());
            $user->setWizard(true);
            $user->setAnalytics(true);
            $user->setSharedata(true);
            $user->setRoles(['ROLE_USER']);
            $em = $managerRegistry->getManagerForClass(User::class);
            $em->persist($user);
            $em->flush();
        }

        //Handle getting or creating the user entity likely with a posted form
        // The third parameter "main" can change according to the name of your firewall in security.yml
        $token = new UsernamePasswordToken($user, "main", $user->getRoles());

        $this->container->get('security.token_storage')->setToken($token);
        //$this->container->get('session')->set('_security_main', serialize($token));

        /*       $this->get('security.token_storage')->setToken($token);

               // If the firewall name is not main, then the set value would be instead:
               // $this->get('session')->set('_security_XXXFIREWALLNAMEXXX', serialize($token));
               $this->get('session')->set('_security_main', serialize($token));
       */
        // Fire the login event manually
        $event = new InteractiveLoginEvent($request, $token);
        $dispatcher = new EventDispatcher();
        $dispatcher->dispatch($event,"security.interactive_login");

        /*$response = Unirest\Request::get(
            'https://api.chucknorris.io/jokes/random'
        );

        if($response && empty($response->body)){
            $requestStack->getSession()->set('chuck_quote', $response->body->value);
        }*/

        $oldToken = $user->getMsToken();

        $msToken = new MsToken();
        $msToken->createFromData($accessToken, $userMS, $user);
        $user->setMsToken($msToken);

        $emUser = $managerRegistry->getManagerForClass(User::class);
        $emUser->persist($user);

        $em = $managerRegistry->getManagerForClass(MsToken::class);
        $em->persist($msToken);
        if($oldToken != null){
            $em->remove($oldToken);
        }

        $emUser->flush();
        $em->flush();

        return true;
        /*return $this->render('msgraph/callback.html.twig', [
            'msUser' => $userMS,
        ]);*/
    }

    /**
     * @Route("/signout", methods="GET", name="msgraph_signout")
     * Cache(smaxage="10")
     */
    #[Route("/signout",name: "msgraph_signout")]
    public function signout(Request $request, ManagerRegistry $managerRegistry): Response
    {
        /**
         * @var $currentUser User
         */
        $currentUser = $this->getUser();

        $em = $managerRegistry->getManagerForClass(User::class);

        if($currentUser->getMsToken() != null){
            $em->remove($currentUser->getMsToken());
        }

        $currentUser->setMsToken(null);
        $em->persist($currentUser);

        $em->flush();

        return $this->redirectToRoute('security_logout');
    }


    public function getAccessToken(User $user, ManagerRegistry $managerRegistry): ?string{
        $msToken = $user->getMsToken();
        if(empty($msToken) || empty($msToken->getAccessToken()) || empty($msToken->getRefreshToken()) || empty($msToken->getTokenExpires())){
            return null;
        }

        // Check if token is expired
        //Get current time + 5 minutes (to allow for time differences)
        $now = time() + 300;
        if ($msToken->getTokenExpires() <= $now || $msToken == null) {
            // Token is expired (or very close to it)
            // so let's refresh

            // Initialize the OAuth client
            $oauthClient = new \League\OAuth2\Client\Provider\GenericProvider([
                'clientId' => $this->getParameter('azure.appId'),
                'clientSecret' => $this->getParameter('azure.appSecret'),
                'redirectUri' => $this->getParameter('azure.redirectUri'),
                'urlAuthorize' => $this->getParameter('azure.authority') . $this->getParameter('azure.authorizeEndpoint'),
                'urlAccessToken' => $this->getParameter('azure.authority') . $this->getParameter('azure.tokenEndpoint'),
                'urlResourceOwnerDetails' => '',
                'scopes' => $this->getParameter('azure.scopes')
            ]);

            try {
                // Make the token request
                $newToken = $oauthClient->getAccessToken('refresh_token', [
                    'refresh_token' => $msToken->getRefreshToken()
                ]);

                $msToken->updateToken($newToken);
                $em = $managerRegistry->getManagerForClass(MsToken::class);
                $em->persist($msToken);
                $em->flush();

                return $newToken->getToken();
            } catch (IdentityProviderException $e) {
                return null;
            }
        }

        return $msToken->getAccessToken();
    }
}
