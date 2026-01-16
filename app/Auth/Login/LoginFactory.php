<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace App\Auth\Login;
class LoginFactory {
    public static function make(string $type): LoginService {
        return match (strtolower($type)) {
            'manager'  => new ManagerLogin(),
            'staff'    => new StaffLogin(),
            'customer' => new CustomerLogin(),
            default    => new CustomerLogin(),
        };
    }
}
