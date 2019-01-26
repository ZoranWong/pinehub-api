<?php

namespace App\Http\Requests\Admin;

use Urameshibr\Requests\FormRequest;

class CountryUpdateRequest extends FormRequest
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
            'code' => ['required', 'string', 'regex:/^[0-9]{1,6}$/'],
            'name' => ['required', 'string', 'max:32']
        ];
    }

    public function messages()
    {
        return [
            'code.required' => '国家编码不能为空',
            'name.required' => '国家名称不能为空',
            'code.string'   => '国家编码不是字符串格式',
            'code.regex'    => '国家编码格式错误',
            'name.string'   => '国家名称不是字符串',
            'name.max'      => '国家名称过长'
        ]; // TODO: Change the autogenerated stub
    }
}
