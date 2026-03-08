<?php

if (! function_exists('base64url_encode')) {
    /**
     * Base64URL encode (RFC 4648) — dipakai untuk build JWT FCM
     */
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
