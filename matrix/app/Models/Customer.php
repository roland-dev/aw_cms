<?php

namespace Matrix\Models;

class Customer extends BaseModel
{
    //
    protected $fillable = ['open_id', 'code', 'name', 'mobile', 'nickname', 'icon_url', 'qy_userid', 'updated_at'];

    public function getCustomerListByName(string $name)
    {
        $customerList = self::where('name', 'like', "%$name%")->get();

        return empty($customerList) ? [] : $customerList->toArray();
    }

    public function getCustomerList(array $openId)
    {
        $customerList = self::whereIn('open_id', $openId)->get();

        return empty($customerList) ? [] : $customerList->toArray();
    }

    public function getCustomer(string $openId)
    {
        $customer = self::where('open_id', $openId)->take(1)->firstOrFail();
        return empty($customer) ? [] : $customer->toArray();
    }
}
