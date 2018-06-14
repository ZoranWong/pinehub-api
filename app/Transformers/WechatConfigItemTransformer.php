<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use App\Entities\WechatConfig as WechatConfigItem;

/**
 * Class WechatConfigItemTransformer.
 *
 * @package namespace App\Transformers;
 */
class WechatConfigItemTransformer extends TransformerAbstract
{
    /**
     * Transform the WechatConfigItem entity.
     *
     * @param WechatConfigItem $model
     *
     * @return array
     */
    public function transform(WechatConfigItem $model)
    {
        return [
            'id'         => (int) $model->id,

            /* place your other model properties here */
            'app_id' => $model->appId,
            'app_name' => $model->appName,
            'mode' => $model->mode,
            'type' => $model->type,
            'wechat_bind_app' => $model->wechatBindApp,
            'created_at' => $model->createdAt,
            'updated_at' => $model->updatedAt
        ];
    }
}