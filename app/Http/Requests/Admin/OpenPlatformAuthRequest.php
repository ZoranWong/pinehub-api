<?php
/**
 * Created by PhpStorm.
 * User: wangzaron
 * Date: 2018/9/10
 * Time: 上午10:35
 */

namespace App\Http\Requests\Admin;


use AlbertCht\Form\FormRequest;

class OpenPlatformAuthRequest extends FormRequest
{
    public function authorize()
    {
        return true; // TODO: Change the autogenerated stub
    }

    public function rules()
    {
        // TODO: Implement rules() method.
        return [
            'app_id' => 'required|exists:apps,id',
            'token'  => 'required',
            'type'   => 'in:official_account,mini_program,all'
        ];
    }
}