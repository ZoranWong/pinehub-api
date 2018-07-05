<?php /** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Wechat;

use App\Entities\App;
use App\Entities\WechatConfig;
use App\Events\UserGetCardEvent;
use App\Events\WechatSubscribeEvent;
use App\Http\Response\JsonResponse;
use App\Listeners\CardCheckEventListener;
use App\Repositories\AppRepository;
use App\Repositories\WechatConfigRepository;

use App\Services\Wechat\OpenPlatform\Guard;

use Dingo\Api\Routing\Helpers;
use EasyWeChat\OpenPlatform\Application;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Overtrue\LaravelWeChat\Controllers\OpenPlatformController as Controller;

class OpenPlatformController extends Controller
{
    //
    use Helpers;
    protected $wechat = null;
    protected $appRepository = null;

    protected $wechatRepository = null;

    public function __construct(AppRepository $appRepository, WechatConfigRepository $wechatConfigRepository)
    {
        $this->wechat = app('wechat');
        $this->appRepository = $appRepository;
        $this->wechatRepository = $wechatConfigRepository;
        //监听事件
    }

    public function __invoke(Application $application)
    {
        $server = $application->server;
        $server->on(Guard::EVENT_SUBSCRIBE, function ($payload) {
            Event::fire(new WechatSubscribeEvent($payload));
        });

        $server->on(Guard::EVENT_GET_CARD, function ($payload) {
            Event::fire(new UserGetCardEvent($payload));
        });

        $server->on(Guard::EVENT_CARD_CHECK_NOT_PASSED, function ($payload) {
            $payload['status'] = 2;
            Event::fire(new CardCheckEventListener($payload));
        });

        $server->on(Guard::EVENT_CARD_CHECK_PASSED, function ($payload) {
            $payload['status'] = 1;
            Event::fire(new CardCheckEventListener($payload));
        });
        return parent::__invoke($application); // TODO: Change the autogenerated stub
    }


    public function componentLoginAuth(Request $request)
    {
        $appId = $request->input('app_id', null);
        $token = $request->input('token', null);
        Cache::set(CURRENT_APP_PREFIX.$request->input('token'), $appId, 3600);
        return view('open-platform.auth')->with('authUrl', $this->wechat->openPlatformComponentLoginPage($appId, $token))->with('success', false);
    }


    public function componentLoginCallback(string $appId, Request $request)
    {
        $app = $this->appRepository->find($appId);
        $authCode = $request->input('auth_code', null);
        $expiresIn = $request->input('expires_in', null);
        $cacheAuthCodeKey = CURRENT_APP_PREFIX.$authCode;
        $cacheTokenKey = CURRENT_APP_PREFIX.$request->input('token', null);
        $wechatAppid = Cache::get($cacheAuthCodeKey, null);
        if($wechatAppid) {
            Cache::delete($cacheAuthCodeKey);
            $wechatMap = $this->wechatRepository->findByField('app_id', $wechatAppid);
            tap($app, function (App $app) use($wechatMap){
                $wechatMap->map(function (WechatConfig $config) use($app){
                    if($config->type === WECHAT_OFFICIAL_ACCOUNT) {
                        $app->wechatAppId = $config->appId;
                        if(!$app->openAppId) {
                            $account = $this->wechat->openPlatform()->officialAccount($config->appId)->account->create();
                            $app->openAppId = $account['open _appid'];
                        }
                        $this->wechat->openPlatform()->officialAccount($config->appId)->account->bindTo($app->openAppId);
                    } else {
                        $app->miniAppId = $config->appId;
                        if(!$app->openAppId) {
                            $account = $this->wechat->openPlatform()->miniProgram($config->appId)->account->create();
                            $app->openAppId = $account['open _appid'];
                        }
                        $this->wechat->openPlatform()->miniProgram($config->appId)->account->bindTo($app->openAppId);
                    }
                    $config->wechatBindApp = $app->id;
                    $config->save();
                    $app->save();
                });
            });
            Cache::set($cacheTokenKey, $wechatAppid, $expiresIn);
        }else{
            if($app && $authCode && $expiresIn) {
                Cache::set($cacheAuthCodeKey, with($app, function (App $app) use($request) {
                    return ['app_id' => $app->id, 'token' => $request->input('token', null)];
                }), $expiresIn);
            }
        }
        return view('open-platform.auth')->with('success', true);
    }

    public function openPlatformAuthMakeSure(Request $request)
    {
        exit('');
        $auth = Cache::get(CURRENT_APP_PREFIX.$request->input('token'), false);
        if($auth) {
            //$app = $this->appRepository->find($request->input('app_id'));
            return $this->response(new JsonResponse($auth));
        }
        return $this->response();
        return $this->response()->error('未完成授权，请等待', HTTP_STATUS_NO_RESPONSE);
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        $this->response = null;
        $this->user = null;
        $this->wechat = null;
        $this->wechatRepository = null;
        $this->auth = null;
        $this->scopes = null;
        $this->api = null;
        $this->rateLimit = null;
        $this->throttles = null;
        $this->authenticationProviders = null;
        $this->appRepository = null;
    }

}
