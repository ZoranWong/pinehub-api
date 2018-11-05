<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/3
 * Time: 14:47
 */

namespace App\Http\Controllers\MiniProgram;

use App\Entities\App;
use App\Entities\MpUser;
use App\Http\Requests\CreateRequest;
use App\Repositories\MpUserRepository;
use App\Repositories\AppRepository;
use App\Repositories\ShopRepository;
use App\Repositories\UserRepository;
use App\Repositories\CustomerTicketCardRepository;
use App\Services\AppManager;
use App\Transformers\Mp\MpUserTransformer;
use App\Transformers\Mp\MpUserInfoMobileTransformer;
use App\Transformers\Mp\AppAccessTransformer;
use App\Transformers\Mp\MvpLoginTransformer;
use Carbon\Carbon;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Response\JsonResponse;
use App\Exceptions\UserCodeException;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    /**
     * @var MpUserRepository|null
     */
    protected  $mpUserRepository = null;

    /**
     * @var ShopRepository|null
     */
    protected  $shopRepository = null;

    /**
     * @var UserRepository|null
     */
    protected  $userRepository = null;

    /**
     * @var CustomerTicketCardRepository|null
     */
    protected  $customerTicketCardRepository = null;

    /**
     * AuthController constructor.
     * @param MpUserRepository $mpUserRepository
     * @param CustomerTicketCardRepository $customerTicketCardRepository
     * @param UserRepository $userRepository
     * @param ShopRepository $shopRepository
     * @param AppRepository $appRepository
     * @param Request $request
     */
    public function __construct( MpUserRepository $mpUserRepository,
                                CustomerTicketCardRepository $customerTicketCardRepository,
                                UserRepository $userRepository,
                                ShopRepository $shopRepository,
                                AppRepository $appRepository,
                                Request $request )
    {
        parent::__construct($request, $appRepository);

        $this->mpUserRepository             = $mpUserRepository;
        $this->appRepository                = $appRepository;
        $this->shopRepository               = $shopRepository;
        $this->userRepository               = $userRepository;
        $this->customerTicketCardRepository = $customerTicketCardRepository;
    }

    /**
     * 注册
     * @param CreateRequest $request
     * @return AuthController|\Dingo\Api\Http\Response|
     * \Dingo\Api\Http\Response\Factory|\Illuminate\Foundation\Application|
     * \Laravel\Lumen\Application|mixed
     */

    public function registerUser(CreateRequest $request)
    {
        $session = $this->session();

        //获取小程序app_id
        $currentApp = app(AppManager::class)->currentApp;

        //根据小程序id和登陆接口返回的解析后的用户信息
        list($errCode, $data) = app()->makeWith('bizDataCrypt', [$currentApp, $session['session_key']])
            ->decryptData($request->input('encrypted_data'), $request->input('iv') );

        if ($errCode == 0) {
            $mpUser = $this->mpUserRepository->create($data);

            $param = [
                'platform_app_id' => $mpUser['platform_app_id'],
                'password' => $mpUser['session_key']
            ];

            $token = Auth::attempt($param);

            $mpUser['token'] = $token;

            return $this->response()
                ->item($mpUser, new MpUserTransformer());

        } else {
            throw new UserCodeException($errCode);
        }
    }

    /**
     * 获取用户信息
     * @return \Dingo\Api\Http\Response
     */
    public function userInfo()
    {
        $user = $this->mpUser();

        $customerTickets = $this->customerTicketCardRepository
            ->findWhere(['customer_id' => $user['id']]);

        $user['ticket_num'] = count($customerTickets);

        $shopUser = $this->shopRepository
            ->findWhere(['user_id' => $user['member_id']])
            ->first();

        $user['shop_id'] = $shopUser['id'];

        return $this->response()
            ->item($user, new MpUserInfoMobileTransformer());
    }

    /**
     * 判断是否为当前的小程序
     * @param string $appid
     * @param string $appSecret
     * @return \Dingo\Api\Http\Response
     */

    public function appAccess(Request $request)
    {
        $request = $request->all();

        $item = $this->appRepository
            ->findWhere(['id'=>$request['app_id']])
            ->first();

        $sign = md5(md5($item['id'].$item['secret']).$request['timestamp']);

        if ($request['sign'] == $sign){
            $accessToken = Hash::make($item['id'], with($item, function (App $app) {
                return $app->toArray();
            }));

            app(AppManager::class)->setCurrentApp($item)
                ->setAccessToken($accessToken);

            $item['access_token'] = $accessToken;

            return $this->response()
                ->item($item, new AppAccessTransformer());
        }else{
            $errCode = 'sign签名错误';
            throw new UserCodeException($errCode);
        }
    }

    /**
     * 用户登陆
     * @param string $code
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
     * @throws \Exception
     */

    public function login(string $code, Request $request)
    {
        $accessToken = $request->input('access_token', null);

        $session = app('wechat')->miniProgram()
            ->auth
            ->session($code);

        cache([$accessToken.'_session'=> $session], 60);

        $mpUser = $this->mpUserRepository
            ->findByField('platform_open_id', $session['openid'])
            ->first();


        if($session && $mpUser) {
            $param = [
                'platform_open_id' => $mpUser['platform_open_id'],
                'password' => $mpUser['session_key']
            ];

            $token = Auth::attempt($param);

            $mpUser['token'] = $token;

            if ($mpUser['member_id']){
                with($mpUser, function (MpUser $mpUser) {
                    $mpUser->member()->update([
                        'last_login_at' => Carbon::now()
                    ]);
                });
            }
            return $this->response()
                ->item($mpUser, new MvpLoginTransformer());
        }

        return $this->response(new JsonResponse(['user_info' => $session]));
    }

    /**
     * 保存手机号
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     */
    public function saveMobile(Request $request)
    {
        $mpUser = $this->mpUser();

        $session = $this->session();

        $currentApp = app(AppManager::class)->currentApp;

        //解密手机号码
        list($errCode, $data) = app()->makeWith('bizDataCrypt', [$currentApp, $session['session_key']])
            ->decryptData($request->input('encrypt_data'), $request->input('iv') );

        if ($errCode == 0) {
            $user = $mpUser->only(['nickname', 'city', 'province', 'city', 'avatar', 'can_use_score', 'total_score',
                'score', 'sex']);

            $user['mobile'] = $data['phoneNumber'];

            $member = $this->userRepository
                ->firstOrCreate($user);

            $mpUser->mobile = $user['mobile'];

            $member->customers()
                ->save($mpUser);

            return $this->response(new JsonResponse(['user_info' => $user]));
        }else{
            throw new UserCodeException($errCode);
        }
    }
}