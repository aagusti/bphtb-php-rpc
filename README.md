# bphtb-php-rpc
Aplikasi ini tergantung kepada
https://github.com/aagusti/opensipkd-rpc

master_config.php harus ada baris berikut ini.

    define('PBB_USE_RPC',TRUE);
    define('JSONRPC_URL','localhost:6543/ws/pbb');
    define('JSONRPC_PORT','80');
    define('JSONRPC_USER','admin');
    define('JSONRPC_PASS','$2a$10$E5u2YX14FAwt/.gx3Jk3Gu.xVa.wY6C4/vn56kFvF8KtTpXjxDnb6');
