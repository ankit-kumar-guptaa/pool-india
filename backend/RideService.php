<?php
class RideService {
    public static function postRide(array $rideData) {
        return ApiService::post('app/PostRide', $rideData);
    }

    public static function searchRides(array $searchCriteria) {
        return ApiService::post('app/PostRide', $searchCriteria); // API uses PostRide with isSearch = 1
    }

    public static function acceptRideRequest(int $requestId) {
        return ApiService::post('App/AcceptRideRequest', ['requestId' => $requestId]);
    }

    public static function rejectRideRequest(int $requestId) {
        return ApiService::post('App/RejectRideRequest', ['requestId' => $requestId]);
    }

    public static function getMyRides(int $userId) {
        return ApiService::post('app/GetMyRideList', ['userId' => $userId]);
    }
}
