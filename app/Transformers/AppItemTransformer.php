<?php

namespace App\Transformers;

use Illuminate\Support\Facades\Auth;
use League\Fractal\TransformerAbstract;
use App\Entities\App as AppItem;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class AppItemTransformer.
 *
 * @package namespace App\Transformers;
 */
class AppItemTransformer extends TransformerAbstract
{
    /**
     * Transform the AppItem entity.
     *
     * @param AppItem $model
     *
     * @return array
     */
    public function transform(AppItem $model)
    {
        return [
            'id'         => $model->id,
            'name' => $model->name,
            'mini_app_id' => $model->miniAppId,
            'wechat_app_id' => $model->wechatAppId,
            'secret' => $model->secret,
            'logo' => $model->logo,
            'contact_phone_num' => $model->contactPhoneNum,
            'contact_name' => $model->contactName,
            /* place your other model properties here */
            'open_platform_auth_url' => buildUrl('web.wxopen', 'auth', [], [
                'app_id' => $model->id, 'token' =>  (string)app('tymon.jwt.auth')->getToken(), 'type' => 'all'
            ]),
            'created_at' => $model->createdAt,
            'updated_at' => $model->updatedAt
        ];
    }
}
