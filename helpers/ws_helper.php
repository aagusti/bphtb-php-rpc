<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('json_rpc_header')){
    function json_rpc_header($userid, $password){
        date_default_timezone_set('UTC'); 
        $inttime     = strval(time()-strtotime('1970-01-01 00:00:00')); 
        $value       = "$userid&" . $inttime; 
        $key         = $password; 
        $signature   = hash_hmac('sha256', $value, $key, true); 
        $signature64 = base64_encode($signature);
        $headers      = array("userid: $userid",
                        "signature:$signature64",
                        "key:$inttime");   	
        return $headers;
    }
}

if (!function_exists('json_rpc_client')){
    function json_rpc_client($method, $params) {
        $headers = json_rpc_header(JSONRPC_USER,JSONRPC_PASS);
        $ch = curl_init();
        $params = array(
            CURLOPT_RETURNTRANSFER => TRUE,
            //CURLOPT_COOKIEJAR => $this->cookie_file->path,
            //CURLOPT_COOKIEFILE => $this->cookie_file->path,
            CURLOPT_URL => JSONRPC_URL,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; zh-CN; rv:1.9.1.13) Gecko/20100914 Firefox/3.5.13( .NET CLR 3.5.30729)',
            CURLOPT_POST => TRUE,
            CURLOPT_CONNECTTIMEOUT=> 5,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POSTFIELDS => json_encode(array(
                'jsonrpc'=> '2.0',
                'method'=> $method,
                'params'=> $params,
                'id'=> 1
            ))
        );
        curl_setopt_array($ch, $params);
        $result = json_decode(curl_exec($ch), TRUE);
        curl_close($ch);
        /*if (!isset($result['jsonrpc']) || $result['jsonrpc'] != '2.0') {
            throw new JSONRPC_Exception('Wrong Response ! It`s Not JSONRPC2.0');
        }
        if ($result['id'] != $this->_id) {
            throw new JSONRPC_Exception('Wrong Response Id');
        }
        if (isset($result['error'])) {
            throw new JSONRPC_Exception($result['error']['message']);
        }*/
        
        return $result; // ['result'];
    }
}
    
if (!function_exists('ws_info_op'))
{
    function ws_info_op($nop,$tahun="") 
    {
        $nop_num = preg_replace("/[^0-9]/", "", $nop);
        if ((!$nop) || strlen($nop_num) != 18)
            return FALSE;

        $nop_dot = preg_replace("/([0-9]{2})([0-9]{2})([0-9]{3})([0-9]{3})([0-9]{3})([0-9]{4})([0-9]{1})/", "$1.$2.$3.$4.$5.$6.$7", $nop_num);
        $kode = explode(".", $nop_dot);
        list($kd_propinsi, $kd_dati2, $kd_kecamatan, $kd_kelurahan, $kd_blok, $no_urut, $kd_jns_op) = $kode;
        $params = array('data'=>array(
                                  array('kode'=>$nop_num,
                                        'tahun'=>$tahun)
                                )
                        );
        return json_rpc_client('get_info_op',$params);          
    }
}

if (!function_exists('ws_get_dop'))
{
    function ws_get_dop($nop) 
    {
        $nop_num = preg_replace("/[^0-9]/", "", $nop);
        if ((!$nop) || strlen($nop_num) != 18)
            return FALSE;
        $params = array('data'=>array(
                                  array('kode'=>$nop)
                                )
                        );
        return json_rpc_client('get_dop',$params);          
    }
}

if (!function_exists('ws_get_sppt'))
{
    function ws_get_sppt($nop, $tahun=0) 
    {
        $nop_num = preg_replace("/[^0-9]/", "", $nop);
        if ((!$nop) || strlen($nop_num) != 18)
            return FALSE;
        $params = array('data'=>array(
                                  array('kode'=>$nop,
                                        'tahun'=>$tahun)
                                )
                        );
        return json_rpc_client('get_sppt',$params);          
    }
}
?>

        