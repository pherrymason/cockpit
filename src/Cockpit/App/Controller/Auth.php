<?php

namespace Cockpit\App\Controller;

use Cockpit\Framework\EventSystem;
use Cockpit\Framework\TemplateController;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use League\Plates\Engine;
use Mezzio\Authentication\Session\PhpSession;
use Mezzio\Authentication\UserInterface;
use Mezzio\Session\LazySession;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\RouteParserInterface;

class Auth extends TemplateController
{
    private const REDIRECT_ATTRIBUTE = 'authentication:redirect';

    /** @var PhpSession */
    private $adapter;
    /** @var EventSystem */
    private $eventSystem;
    /** @var RouteParserInterface */
    private $router;

    public function __construct(Engine $templateEngine, ContainerInterface $container, EventSystem $eventSystem, PhpSession $adapter, RouteParserInterface $router)
    {
        parent::__construct($templateEngine, $container);
        $this->eventSystem = $eventSystem;
        $this->adapter = $adapter;
        $this->router = $router;
    }

    public function login(ServerRequestInterface $request, ResponseInterface $response)
    {
        $session = $request->getAttribute('session');
        $redirect = $this->getRedirect($request, $session);

        // Handle submitted credentials
        if ('POST' === $request->getMethod()) {
            return $this->handleLoginAttempt($request, $session, $redirect);
        }

        // Display initial login form
        $session->set(self::REDIRECT_ATTRIBUTE, $redirect);
        return $this->renderResponseLayout($request, 'cockpit::views/layouts/login');
    }

    private function getRedirect(
        ServerRequestInterface $request,
        SessionInterface $session
    ): string
    {
//        $redirect = $session->get(self::REDIRECT_ATTRIBUTE);

//        if (! $redirect) {
//            $redirect = $request->getHeaderLine('Referer');
//            $loginURL = $this->router->fullUrlFor(URL::site(), 'login');
//            if (in_array($redirect, ['', $this->router->urlFor('login')], true)) {
        $redirect = $this->router->urlFor('home');
//            }
//        }

        return $redirect;
    }

    private function handleLoginAttempt(
        ServerRequestInterface $request,
        SessionInterface $session,
        string $redirect
    ): ResponseInterface
    {
        // User session takes precedence over user/pass POST in
        // the auth adapter so we remove the session prior
        // to auth attempt
        $session->unset(UserInterface::class);

        // Login was successful
        if ($this->adapter->authenticate($request)) {
            $session->unset(self::REDIRECT_ATTRIBUTE);

            // @todo Redirect if request content-type is not json
//            return new RedirectResponse($redirect);
            return new JsonResponse(
                [
                    'success' => true,
                    'user' => []
                ]
            );
        }

        // Login failed
        return new HtmlResponse(
            $this->renderer->render(
                'app::login',
                ['error' => 'Invalid credentials; please try again']
            )
        );
    }

    public function logout(ServerRequestInterface $request): ResponseInterface
    {
        /** @var LazySession $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        $session->clear(); // does not destroy the session (only clearing data)
        session_destroy(); // everything works however we don't use SessionInterface

        return new RedirectResponse('/admin');
    }

    public function forgotpassword()
    {

        return $this->render('cockpit:views/layouts/forgotpassword.php');
    }

    public function requestreset()
    {

        if ($user = $params['user']) {

            $query = ['active' => true];

            if ($this->app->helper('utils')->isEmail($user)) {
                $query['email'] = $user;
            } else {
                $query['user'] = $user;
            }

            $user = $this->app->storage->findOne('cockpit/accounts', $query);

            if (!$user) {
                return $this->stop(['error' => $this('i18n')->get('User does not exist')], 404);
            }

            $token = uniqid('rp-' . bin2hex(random_bytes(16)));
            $target = $this->app->param('', $this->app->getSiteUrl(true) . '/auth/newpassword');
            $data = ['_id' => $user['_id'], '_reset_token' => $token];

            $this->app->storage->save('cockpit/accounts', $data);
            $message = $this->app->view('cockpit:emails/recover.php', compact('user', 'token', 'target'));

            try {
                $response = $this->app->mailer->mail(
                    $user['email'],
                    $params['subject'] ?? $this->app->getSiteUrl() . ' - ' . $this('i18n')->get('Password Recovery'),
                    $message
                );
            } catch (\Exception $e) {
                $response = $e->getMessage();
            }

            if ($response !== true) {
                return $this->stop(['error' => $this('i18n')->get($response)], 404);
            }

            return ['message' => $this('i18n')->get('Recovery email sent')];
        }

        return $this->stop(['error' => $this('i18n')->get('User required')], 412);
    }

    public function newpassword()
    {

        if ($token = $params['token']) {

            $user = $this->app->storage->findOne('cockpit/accounts', ['_reset_token' => $token]);

            if (!$user) {
                return false;
            }

            $user['md5email'] = md5($user['email']);

            return $this->render('cockpit:views/layouts/newpassword.php', compact('user', 'token'));
        }

        return false;

    }

    public function resetpassword()
    {

        if ($token = $params['token']) {

            $user = $this->app->storage->findOne('cockpit/accounts', ['_reset_token' => $token]);
            $password = trim($params['password']);

            if (!$user || !$password) {
                return false;
            }

            $data = ['_id' => $user['_id'], 'password' => $this->app->hash($password), '_reset_token' => null];

            $this->app->storage->save('cockpit/accounts', $data);

            return ['success' => true, 'message' => $this('i18n')->get('Password updated')];
        }

        return $this->stop(['error' => $this('i18n')->get('Token required')], 412);
    }
}
