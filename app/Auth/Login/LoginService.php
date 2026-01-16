<?php

/**
 * Author: Chiang Jong Sheng
 */

namespace App\Auth\Login;
interface LoginService {
    /** Attempt and return redirect route name on success. */
    public function attempt(array $credentials): string;
}
