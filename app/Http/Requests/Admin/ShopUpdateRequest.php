<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class ShopUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            //
            'name'           => [ 'string', 'size:32'],
            'user_id'        => ['integer', 'exists:users,id'],
            'description'    => ['string'],
            'country_id'     => ['integer', 'exists:countries,id'],
            'province_id'    => ['integer', 'exists:provinces,id'],
            'city_id'        => ['integer', 'exists:cities,id'],
            'county_id'      => ['integer', 'exists:counties,id'],
            'address'        => ['string'],
            'lng'            => ['numeric'],
            'lat'            => [ 'numeric'],
            'manager_mobile' => ['regex:'.MOBILE_PATTERN, 'not_exists:users,mobile'],
            'manager_name'   => ['string', 'max:16'],
            'status'         => ['integer', 'in:0,1,2,3']
        ];
    }
}
