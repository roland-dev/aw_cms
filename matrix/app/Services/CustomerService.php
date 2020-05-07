<?php
namespace Matrix\Services;

use Matrix\Contracts\CustomerManager;

use Matrix\Models\Customer;
use Matrix\Exceptions\MatrixException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerService extends BaseService implements CustomerManager
{
    private $customer;

    public function __construct(Customer $customer)
    {
        $this->customer = $customer;
    }

    public function updateCustomer(array $customerInfo)
    {
        $customer = $this->customer->updateOrCreate([
            'open_id' => array_get($customerInfo, 'open_id'),
        ], $customerInfo);
        return $customer->toArray();
    }

    public function getCustomerListByName(string $name)
    {
        $customerList = $this->customer->getCustomerListByName($name);

        return $customerList;
    }

    public function getCustomerList(array $openIdList)
    {
        $customerList = $this->customer->getCustomerList($openIdList);
        return $customerList;
    }

    public function getCustomerByQyUserid(string $qyUserid)
    {
        try {
            $customer = Customer::where('qy_userid', $qyUserid)->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new VideoException("{$qyUserid}我没找着这个客户.", SYS_STATUS_ERROR_UNKNOW);
        }

        return $customer;
    }
}

