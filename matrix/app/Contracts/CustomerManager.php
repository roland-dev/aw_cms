<?php
namespace Matrix\Contracts;

interface CustomerManager extends BaseInterface
{
    public function updateCustomer(array $customerInfo);
    public function getCustomerListByName(string $name);
    public function getCustomerList(array $openIdList);
    public function getCustomerByQyUserid(string $qyUserid);
}

