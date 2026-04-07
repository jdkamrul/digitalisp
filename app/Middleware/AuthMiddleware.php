<?php

class AuthMiddleware {
    public function handle(): void {
        if (!isset($_SESSION['user_id'])) {
            // For API requests
            if (str_contains($_SERVER['REQUEST_URI'], '/api/')) {
                jsonResponse(['error' => 'Unauthorized', 'code' => 401], 401);
            }
            redirect(base_url('login'));
        }
    }
}
