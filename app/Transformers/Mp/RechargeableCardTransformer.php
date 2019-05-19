<?php
/**
 * RechargeableCardTransformer.php
 * User: katherine
 * Date: 19-5-17 下午1:46
 */

namespace App\Transformers\Mp;

use App\Entities\RechargeableCard;
use League\Fractal\TransformerAbstract;

class RechargeableCardTransformer extends TransformerAbstract
{
    public function transform(RechargeableCard $rechargeableCard)
    {
        return [
            'id' => $rechargeableCard->id,
            'name' => $rechargeableCard->name,
            'price' => $rechargeableCard->price / 100,
            'amount' => $rechargeableCard->amount / 100,
            'on_sale' => $rechargeableCard->onSale,
            'preferential_price' => $rechargeableCard->preferentialPrice / 100,
            'auto_renew_price' => $rechargeableCard->autoRenewPrice / 100,
            'card_type_desc' => $rechargeableCard->cardTypeDesc,
            'type_desc' => $rechargeableCard->typeDesc,
            'discount' => $rechargeableCard->discount,
            'usage_scenarios_desc' => $rechargeableCard->usageScenariosDesc
        ];
    }
}