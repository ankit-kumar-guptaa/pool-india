<?php
class AuthService {
    public static function sendOTP(string $phone) {
        return ApiService::get("Ride/SendOTP?MobileNo=$phone");
    }

    public static function verifyUserPhone(string $phone) {
        $payload = [
            'spName' => 'CORP_Login_Phone', 
            'payload' => json_encode(['mobile_No' => $phone])
        ];
        return ApiService::post('Ride/GetDataFromServer', $payload);
    }
}
