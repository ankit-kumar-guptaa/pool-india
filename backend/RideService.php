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
        return ApiService::get("App/GetMyRideList?UserID=$userId");
    }

    public static function getMyConnections(int $userId) {
        $payload = [
            'spName' => 'usp_GreenCar_GetMyConnection',
            'payload' => json_encode(['user_id' => $userId])
        ];
        return ApiService::post('Ride/GetDataFromServer', $payload);
    }

    public static function getCo2Details(int $userId) {
        return ApiService::get("App/GetCo2Details?userid=$userId");
    }
}
