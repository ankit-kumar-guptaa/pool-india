<?php
class ProfileService {
    public static function getUserProfile(string $mobileNo) {
        return ApiService::get("App/GetUserProfile?mobileNo=$mobileNo");
    }

    public static function updateProfile(array $profileData) {
        return ApiService::post('app/ProfileUpdate', $profileData);
    }

    public static function sendAadharOTP(string $aadharNumber) {
        return ApiService::post('App/SendAadharOTP', ['aadharNumber' => $aadharNumber]);
    }

    public static function verifyAadharOTP(string $aadharNumber, string $otp) {
        return ApiService::post('App/VerifyAadharOTP', ['aadharNumber' => $aadharNumber, 'otp' => $otp]);
    }
}
