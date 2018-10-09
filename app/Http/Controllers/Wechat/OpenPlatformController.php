<?php /** @noinspection PhpUndefinedClassInspection */

namespace App\Http\Controllers\Wechat;

use App\Entities\App;
use App\Http\Requests\Admin\OpenPlatformAuthCallbackRequest as AuthCallbackRequest;
use App\Http\Requests\Admin\OpenPlatformAuthRequest as AuthRequest;
use App\Repositories\AppRepository;
use App\Repositories\WechatConfigRepository;
use Dingo\Api\Routing\Helpers;
use EasyWeChat\OpenPlatform\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Overtrue\LaravelWeChat\Controllers\OpenPlatformController as Controller;
use Overtrue\LaravelWeChat\Events\OpenPlatform\Authorized;

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

    /**
     * @param Application $application
     * @return mixed
     * @throws
     * */
    public function __invoke(Application $application)
    {
        return parent::__invoke($application); // TODO: Change the autogenerated stub
    }

    /**
     * @param string $appId
     * @return mixed
     * @throws
     * */
    public function serve(string $appId = null)
    {
        return app('wechat')->openPlatformServerHandle($appId);
    }


    public function componentAuth(AuthRequest $request)
    {
        $appId = $request->input('app_id', null);
        $token = $request->input('token', null);
        $type = $request->input('type', 'all');
        return view('open-platform.auth')->with('authUrl', $this->wechat->openPlatformComponentAuthPage($appId, $token, $type))
            ->with('success', false);
    }


    /**
     * @param string $appId
     * @param AuthCallbackRequest $request
     * @return View
     * @throws
     * */
    public function componentAuthCallback(string $appId,  AuthCallbackRequest $request)
    {
        $app = $this->appRepository->find($appId);
        $authCode = $request->input('auth_code', null);
        $expiresIn = $request->input('expires_in', null);
        $token = $request->input('token', null);
        if($app && $authCode && $expiresIn) {
            $authInfo = $this->wechat->openPlatform()->handleAuthorize($authCode);
            $payload = $authInfo['authorization_info'];
            $payload['app_id'] = $appId;
            $payload['auth_code'] = $authCode;
            $payload['auth_code_expires_in'] = $expiresIn;

            Event::fire(new Authorized($payload));

            Cache::set(CURRENT_APP_PREFIX.$authCode, with($app, function (App $app) use($request, $token) {
                return ['app_id' => $app->id, 'token' => $token];
            }), $expiresIn);
        }
        return view('open-platform.auth')->with('success', true);
    }

    public function openPlatformAuthMakeSure(Request $request)
    {
        $token = $request->bearerToken() ?? $request->input('token');
        $auth = Cache::get(CURRENT_APP_PREFIX.$token, false);
        if($auth) {
            return jsonResponse($auth);
        }
        $this->response()->error('未完成授权，请等待', HTTP_STATUS_NO_RESPONSE);
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
