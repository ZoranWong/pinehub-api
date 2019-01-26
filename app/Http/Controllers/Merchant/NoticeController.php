<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/9/3
 * Time: 14:47
 */

namespace App\Http\Controllers\Merchant;

use App\Repositories\ShopRepository;
use App\Transformers\Merchant\ShopTransformer;
use Carbon\Carbon;
use Dingo\Api\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Jormin\BaiduSpeech\BaiduSpeech;


class NoticeController extends Controller
{


    /**
     * @param int $id
     * @param Request $request
     * @return \Dingo\Api\Http\Response
     * @throws
     */
    public function notice(int $id, Request $request) {
        $shop = app(ShopRepository::class)->todayOrderInfo($id);
        $token = $request->bearerToken() ?: $request->input('token');
        $manager = Auth::user();
        $tokenMeta = null;
        if(cache($token) - time() < 10) {
            $token = Auth::login($manager);
            $tokenMeta = [
                'value' => $token,
                'ttl' => Carbon::now(config('app.timezone'))->addMinute(config('jwt.ttl')),
                'refresh_ttl' => Carbon::now(config('app.timezone'))->addMinute(config('jwt.refresh_ttl'))
            ];
            cache([$token => $tokenMeta['ttl']], $tokenMeta['ttl']);
        }
        $key = "shop.{$id}.order.paid";
        $messages = cache($key);
        $voices = null;
        if($messages) {
            cache()->delete($key);
            $voices = [];
            foreach ($messages as $message) {
                $result = BaiduSpeech::combine($message);
                if($result['success']) {
                    $file = $result['filename'];
                    array_push($voices, Storage::get($file));
                }
            }
        }
        return $this->response->item($shop, new ShopTransformer())
            ->addMeta('token', $tokenMeta)
            ->addMeta('voices', $voices);
    }
}
