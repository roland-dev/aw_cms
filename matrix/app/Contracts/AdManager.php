<?php

namespace Matrix\Contracts;


use Matrix\Models\User;

interface AdManager extends BaseInterface
{
    public function createAd(array $adData, array $terminalIds);

    public function updateAd(int $adId, array $adData, array $terminalIds);

    public function detail(int $adId);

    public function destoryAd(int $adId);

    public function getAdLocations();

    public function getTerminals();

    public function getTerminalsOfLocationCode(string $locationCode);

    public function getMediaTypes();

    public function getOperationTypes();

    public function getAdsData(string $locationCode, array $adIdsOfPermission, string $terminalCode);

    public function getAdAccessIdDatasBylocationCodes(array $locationCodes, array $adIdsOfPermission, string $terminalCode);

    public function getAdList(int $pageNo, int $pageSize, array $credentials);

    public function getAdCnt(array $credentials);

    public function getAdListBySpecialLocationCodes(array $locationCodes, array $adIdsOfPermission, string $terminalCode, int $expiresTime);
}