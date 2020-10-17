<?php
/**
 * This file is part of the Cockpit project.
 *
 * (c) Artur Heinze - 🅰🅶🅴🅽🆃🅴🅹🅾, http://agentejo.com
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Cockpit\App\Controller;

use Cockpit\App\UserRequestExtractor;
use Cockpit\Framework\InputValidationHelpers;
use Cockpit\Framework\TemplateController;
use Cockpit\User\UserRepository;
use Cockpit\User\UserSerializer;
use League\Plates\Engine;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpNotFoundException;

class Accounts extends TemplateController
{
    /** @var UserRepository */
    private $users;
    /** @var UserSerializer */
    private $userSerializer;

    public function __construct(
        \Cockpit\User\UserSerializer $userSerializer,
        UserRepository $users,
        Engine $templateEngine,
        \Psr\Container\ContainerInterface $container
    ) {
        parent::__construct($templateEngine, $container);
        $this->users = $users;
        $this->userSerializer = $userSerializer;
    }


    public function index() {

        if (!$this->module('cockpit')->hasaccess('cockpit', 'accounts')) {
            return $this->helper('admin')->denyRequest();
        }

        $current  = $this->user['_id'];
        $groups   = $this->module('cockpit')->getGroups();

        return $this->render('cockpit:views/accounts/index.php', compact('current', 'groups'));
    }


    public function account(ServerRequestInterface $request, $response)
    {
        $user = $this->extractUser($request);
        if (!$user) {
            throw new HttpNotFoundException($request);
        }

        $uid = $user->id();

//        if (!$this->module('cockpit')->hasaccess('cockpit', 'accounts') && $uid != $this->user['_id']) {
//            return $this->helper('admin')->denyRequest();
//        }

        $account = $this->users->byId($uid);
        if (!$account) {
            throw new HttpNotFoundException($request);
        }
//
//        $this->app['user'] = $this->user;
//
//        if (!$account) {
//            return false;
//        }

//        unset($account["password"]);

//        $fields    = $this->app->retrieve('config/account/fields', null);
        $languages = $this->getLanguages();
//        $groups    = $this->module('cockpit')->getGroups();

//        if (!$this->app->helper('admin')->isResourceEditableByCurrentUser($uid, $meta)) {
//            return $this->render('cockpit:views/base/locked.php', compact('meta'));
//        }
//
//        $this->app->helper('admin')->lockResourceId($uid);
//
//        $this->app->trigger('cockpit.account.fields', [&$fields, &$account]);

        return $this->renderResponse(
            $request,
            'cockpit::views/accounts/account',
            [
                'userSerializer' => $this->userSerializer,
                'visitor' => $user,
                'account' => $account,
                'languages' => [],
                'groups' => [],
                'fields' => []
            ]
        );

//        return $this->render('cockpit:views/accounts/account.php', compact('account', 'uid', 'languages', 'groups', 'fields'));
    }

    public function create() {

        if (!$this->module('cockpit')->hasaccess('cockpit', 'accounts')) {
            return $this->helper('admin')->denyRequest();
        }

        $uid       = null;
        $account   = [
            'user'   => '',
            'email'  => '',
            'active' => true,
            'group'  => 'admin',
            'i18n'   => $this->app->helper('i18n')->locale
        ];

        $fields    = $this->app->retrieve('config/account/fields', null);
        $languages = $this->getLanguages();
        $groups    = $this->module('cockpit')->getGroups();

        $this->app->trigger('cockpit.account.fields', [&$fields, &$account]);

        return $this->render('cockpit:views/accounts/account.php', compact('account', 'uid', 'languages', 'groups', 'fields'));
    }

    public function save(ServerRequestInterface $request)
    {
        $user = $this->extractUser($request);
        $data = $request->getParsedBody()['account'] ?? [];


            // check rights
//            if (!$this->module('cockpit')->hasaccess('cockpit', 'accounts')) {
//
//                if (!isset($data['_id']) || $data['_id'] != $this->user['_id']) {
//                    return $this->helper('admin')->denyRequest();
//                }
//            }

            $data['_modified'] = time();
            $isUpdate = false;

            if (!isset($data['_id'])) {
                // new user needs a password
                if (!isset($data['password']) || !trim($data['password'])) {
                    return new JsonResponse(['error' => 'User password required'], 412);
                }

                if (!isset($data['user']) || !trim($data['user'])) {
                    return new JsonResponse(['error' => 'User required'], 412);
                }

                $data['_created'] = $data['_modified'];
            } else {
//                if (!$this->app->helper('admin')->isResourceEditableByCurrentUser($data['_id'])) {
//                    $this->stop(['error' => "Saving failed! Account is locked!"], 412);
//                }

                $isUpdate = true;
            }

//            if (isset($data['group']) && !$this->module('cockpit')->hasaccess('cockpit', 'accounts')) {
//                unset($data['group']);
//            }

            if (isset($data['password'])) {
                if (strlen($data['password'])){
                    $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
                } else {
                    unset($data['password']);
                }
            }

            if (isset($data['email']) && !InputValidationHelpers::isEmail($data['email'])) {
                return new JsonResponse(['error' => 'Valid email required'], 412);
            }

            if (isset($data['user']) && !trim($data['user'])) {
                return new JsonResponse(['error' => 'Username cannot be empty!'], 412);
            }

            foreach (['name', 'user', 'email'] as $key) {
                if (isset($data[$key])) {
                    $data[$key] = strip_tags(trim($data[$key]));
                }
            }

            // unique check
            // --
            if (isset($data['user'])) {

                $_account = $this->users->byUser($data['user']);

                if ($_account && (!isset($data['_id']) || $data['_id'] != $_account->id())) {
                    return new JsonResponse(['error' =>  'Username is already used!'], 412);
                }
            }

            if (isset($data['email'])) {

                $_account = $this->users->byEmail($data['email']);

                if ($_account && (!isset($data['_id']) || $data['_id'] != $_account->id())) {
                    return new JsonResponse(['error' =>  'Email is already used!'], 412);
                }
            }
            // --

//            $this->app->trigger('cockpit.accounts.save', [&$data, isset($data['_id'])]);
            $this->users->save($data);

            $userSaved = $this->users->byId($data['_id']);

//            if (isset($data['password'])) {
//                unset($data['password']);
//            }
//
//            if (isset($data['_reset_token'])) {
//                unset($data['_reset_token']);
//            }

            if ($userSaved->id() == $user->id()) {
                // TODO Update session user.
                //$this->module('cockpit')->setUser($userSaved);
            }

//            if (!$isUpdate) {
//                $this->app->helper('admin')->lockResourceId($data['_id']);
//            }

            return new JsonResponse($this->userSerializer->serialize($userSaved));
    }

    public function remove() {

        if (!$this->module('cockpit')->hasaccess('cockpit', 'accounts')) {
            return $this->helper('admin')->denyRequest();
        }

        if ($data = $this->param('account', false)) {

            // user can't delete himself
            if ($data['_id'] != $this->user['_id']) {

                $this->app->storage->remove('cockpit/accounts', ['_id' => $data['_id']]);

                return '{"success":true}';
            }
        }

        return false;
    }

    public function find(RequestInterface $request, ResponseInterface $response)
    {
//        \session_write_close();
        $input = $request->getParsedBody();
        $options = array_merge([
            'sort'   => ['user' => 1]
        ], $input['options'] ?? []);

        if (isset($options['filter'])) {

            if (is_string($options['filter'])) {

                if ($filter = json_decode($options['filter'], true)) {
                    $options['filter'] = $filter;
                } else {

                    $options['filter'] = [
                        '$or' => [
                            ['name' => ['$regex' => $options['filter']]],
                            ['user' => ['$regex' => $options['filter']]],
                            ['email' => ['$regex' => $options['filter']]],
                        ]
                    ];
                }
            }
        }

        /*
        $accounts = $this->app->storage->find('cockpit/accounts', $options)->toArray();
        $count    = (!isset($options['skip']) && !isset($options['limit'])) ? count($accounts) : $this->app->storage->count('cockpit/accounts', isset($options['filter']) ? $options['filter'] : []);
        $pages    = isset($options['limit']) ? ceil($count / $options['limit']) : 1;
        $page     = 1;

        if ($pages > 1 && isset($options['skip'])) {
            $page = ceil($options['skip'] / $options['limit']) + 1;
        }

        foreach ($accounts as &$account) {
            unset($account['password'], $account['api_key'], $account['_reset_token']);
            $this->app->trigger('cockpit.accounts.disguise', [&$account]);
        }
        */
        $accounts = [];
        $count= 0;
        $pages = 0;
        $page = 0;

        return new JsonResponse(
            ['accounts' =>$accounts, 'count' => $count, 'pages' => $pages, 'page' => $page]
        );
    }

    protected function getLanguages() {

        $languages = [['i18n' => 'en', 'language' => 'English']];
        /*
        foreach ($this->app->helper('fs')->ls('*.php', '#config:cockpit/i18n') as $file) {

            $lang     = include($file->getRealPath());
            $i18n     = $file->getBasename('.php');
            $language = $lang['@meta']['language'] ?? $i18n;

            $languages[] = ['i18n' => $i18n, 'language'=> $language];
        }*/

        return $languages;
    }

}
