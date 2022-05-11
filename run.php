<?php
error_reporting(0);
date_default_timezone_set("Asia/Jakarta");
require __DIR__ . '/vendor/autoload.php';
require_once __DIR__.'/userAgent.php';
require __DIR__ . '/smshub.php';

//APIKEY SMSHUB
$key = '129713U45ff4ef565a0d9870ababb5d2e760a4c';

if($key == null) {
    die('Masukan apikey di line 9 goblog!!');
}
echo '----------- AUTO REFF ONEASET WITH SMSHUB -----------'.PHP_EOL.PHP_EOL;
use Curl\Curl;
$agent = new userAgent();

$sms = new SMSHub($key);
$saldo = $sms->getBalance();
echo '[ '.date('H:i:s').' ] Saldo SMSHub: '.$saldo.' RUB'.PHP_EOL;
$reff = input('[ '.date('H:i:s').' ] Kode Reff u tod');
$jumlah = input('[ '.date('H:i:s').' ] Mau Berapa reff tod');
for ($ia=0; $ia < $jumlah; $ia++) {
    
    //Random Cookies
    $identity_anonymous_id = RandomUUID(14).'-'.RandomUUID(14).'-'.RandomUUID(8).'-'.RandomUUID(7).'-'.RandomUUID(14);
    $identity_cookie_id = RandomUUID(14).'-'.RandomUUID(15).'-'.RandomUUID(8).'-'.RandomUUID(6).'-'.RandomUUID(14);

    //Random Device
    $deviceId = vsprintf( '%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex(random_bytes(16)), 4) );
    $uuid = RandomUUID(13).'-'.RandomUUID(6).'-'.RandomUUID(8).'-'.RandomUUID(4).'-'.RandomUUID(4).'-'.RandomUUID(4).'-'.RandomUUID(12);
    $deviceToken = RandomDeviceToken(163);
    $aId = RandomUUID(32);
    $ad = RandomUUID(52);


    //Cookies 
    $encodeIdentities = base64_encode('{"$identity_anonymous_id":"'.$identity_anonymous_id.'","$identity_cookie_id":"'.$identity_cookie_id.'"}');
    $encodeCookies = urlencode('sensorsdata2015jssdkcross={"distinct_id":"'.$identity_anonymous_id.'","first_id":"","props":{"$latest_traffic_source_type":"直接流量","$latest_search_keyword":"未取到值_直接打开","$latest_referrer":""},"identities":"'.$encodeIdentities.'","history_login_id":{"name":"","value":""},"$device_id":"'.$deviceId.'"}');
    $cookies = $encodeCookies.'; languageCode=in';

    $no = $ia+1;
    echo '-----------------------------------------------------'.PHP_EOL;
    $getnum = $sms->getNumber('aj', '6', '0', 'any');
    if (is_array($getnum)) {
        $id = $getnum['id'];
        $num = $getnum['number'];
        $nomorHP = str_replace('628', '08', $num);
        echo '[ '.date('H:i:s').' ] Mencoba mendaftar dengan Nomor '.$nomorHP;

        $curl = new Curl();
        $curl->setHeader('Host', 'app.oneaset.co.id');
        $curl->setHeader('Accept', 'application/json, text/plain, */*');
        $curl->setHeader('countryId', '1');
        $curl->setUserAgent('User-Agent', $agent->generate('android'));
        $curl->setHeader('languageId', '123');
        $curl->setHeader('Sec-Fetch-Site', 'same-origin');
        $curl->setHeader('Sec-Fetch-Mode', 'cors');
        $curl->setHeader('Sec-Fetch-Dest', 'empty');
        $curl->setReferrer('Referer', 'https://app.oneaset.co.id/finance/Finance/LandingPage?channel=web_OneAset_activity_financeinvite&referrerCode='.$reff.'&source=outside&ad='.$ad.'');
        $curl->setHeader('Accept-Language', 'en-US,en;q=0.9');
        $curl->setHeader('Cookie', $cookies);
        $curl->get('https://app.oneaset.co.id/api/app/user/sms/captcha?phoneNumber='.$nomorHP.'&imageCaptcha=&smsBizType=1');
        if ($curl->error) {
            $status = $sms->setStatus($id, 8);
            echo '[ '.date('H:i:s').' ] Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
        } else {
            if($curl->response->success == true) {
                echo ' -> Sukses Mengirim OTP'.PHP_EOL;
                echo '[ '.date('H:i:s').' ] Menunggu OTP bos';
                $getOTP = 0;
                do {
                    $curl = new Curl();
                    $curl->post('https://smshub.org/stubs/handler_api.php', 'action=getCurrentActivations&api_key='.$key.'&order=id&orderBy=asc&start=0&length=10');
                    $otpBos = json_decode($curl->response)->array[0]->code;
                    $getOTP++;
                        if ($getOTP == 10) {
                            echo ".";
                            $getOTP = 0;
                        }
                } while (strlen($otpBos) !== 5);
                echo PHP_EOL.'[ '.date('H:i:s').' ] OTP: '.$otpBos.PHP_EOL;
                $curl = new Curl();
                $curl->setHeader('Host', 'app.oneaset.co.id');
                $curl->setHeader('Accept', 'application/json, text/plain, */*');
                $curl->setHeader('countryId', '1');
                $curl->setHeader('User-Agent', $agent->generate('android'));
                $curl->setHeader('languageId', '123');
                $curl->setHeader('Sec-Fetch-Site', 'same-origin');
                $curl->setHeader('Sec-Fetch-Mode', 'cors');
                $curl->setHeader('Sec-Fetch-Dest', 'empty');
                $curl->setUserAgent('Referer', 'https://app.oneaset.co.id/finance/Finance/LandingPage?channel=web_OneAset_activity_financeinvite&referrerCode='.$reff.'&source=outside&ad='.$ad.'');
                $curl->setHeader('Accept-Language', 'en-US,en;q=0.9');
                $curl->setHeader('Cookie', $cookies);
                $curl->post('https://app.oneaset.co.id/api/app/user/register', 'phoneNumber='.$nomorHP.'&captcha='.$otpBos.'&channel=web_OneAset_activity_financeinvite&referrerCode='.$reff.'');
                if ($curl->error) {
                    $status = $sms->setStatus($id, 6);
                    echo '[ '.date('H:i:s').' ] Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
                } else {
                    if($curl->response->success == true) {
                        echo '[ '.date('H:i:s').' ] Sukses Mendaftar..'.PHP_EOL;
                        echo '[ '.date('H:i:s').' ] Mencoba login dengan Nomor '.$nomorHP;
                        $curl = new Curl();
                        $curl->setHeader('versionCode', '12');
                        $curl->setHeader('versionName', '1.0.4');
                        $curl->setHeader('countryId', '1');
                        $curl->setHeader('languageId', '123');
                        $curl->setHeader('deviceType', '4');
                        $curl->setHeader('uuid', $uuid);
                        $curl->setHeader('deviceId', $deviceId);
                        $curl->setHeader('deviceToken', $deviceToken);
                        $curl->setHeader('Host', 'app.oneaset.co.id');
                        $curl->setHeader('User-Agent', 'okhttp/3.12.1');
                        $curl->get('https://app.oneaset.co.id/api/app/user/sms/captcha?phoneNumber='.$nomorHP.'&imageCaptcha=&smsBizType=2');
                        if ($curl->error) {
                            $status = $sms->setStatus($id, 8);
                            echo '[ '.date('H:i:s').' ] Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
                        } else {
                            if($curl->response->success == true) {
                                echo ' -> Sukses Mengirim OTP'.PHP_EOL;
                                $otpLogin = $sms->setStatus($id, 3);
                                echo '[ '.date('H:i:s').' ] Menunggu OTP bos';
                                $getOTP1 = 0;
                                do {
                                    $curl = new Curl();
                                    $curl->post('https://smshub.org/stubs/handler_api.php', 'action=getCurrentActivations&api_key='.$key.'&order=id&orderBy=asc&start=0&length=10');
                                    $otpBos1 = json_decode($curl->response)->array[0]->code;
                                    $getOTP1++;
                                        if ($getOTP1 == 10) {
                                            echo ".";
                                            $getOTP1 = 0;
                                        }
                                } while (strlen($otpBos1) !== 5);
                                echo PHP_EOL.'[ '.date('H:i:s').' ] OTP: '.$otpBos1.PHP_EOL;
                                $curl = new Curl();
                                $curl->setHeader('versionCode', '12');
                                $curl->setHeader('versionName', '1.0.4');
                                $curl->setHeader('countryId', '1');
                                $curl->setHeader('languageId', '123');
                                $curl->setHeader('deviceType', '4');
                                $curl->setHeader('uuid', $uuid);
                                $curl->setHeader('deviceId', $deviceId);
                                $curl->setHeader('deviceToken', $deviceToken);
                                $curl->setHeader('Content-Type', 'application/x-www-form-urlencoded');
                                $curl->setHeader('Content-Length', '63');
                                $curl->setHeader('Host', 'app.oneaset.co.id');
                                $curl->setHeader('User-Agent', 'okhttp/3.12.1');
                                $curl->post('https://app.oneaset.co.id/api/app/user/login', 'phoneNumber='.$nomorHP.'&password='.$otpBos1.'&passwordType=3&captcha=');
                                if ($curl->error) {
                                    $status = $sms->setStatus($id, 6);
                                    echo '[ '.date('H:i:s').' ] Error: ' . $curl->errorCode . ': ' . $curl->errorMessage . "\n";
                                } else {
                                    if($curl->response->success == true) {
                                        $status = $sms->setStatus($id, 6);
                                        echo '[ '.date('H:i:s').' ] Sukses Login dan menggunakan Reff '.$reff.' -> '.$no.PHP_EOL;
                                        // sleep(30);
                                    } else {
                                        $status = $sms->setStatus($id, 6);
                                        exit('[ '.date('H:i:s').' ] Gagal Login');
                                    }
                                }
                            } else {
                                $status = $sms->setStatus($id, 8);
                                exit('[ '.date('H:i:s').' ] Gagal Mengirim OTP Login');         
                            }
                        }
                    } else {
                        $status = $sms->setStatus($id, 6);
                        exit('[ '.date('H:i:s').' ] Gagal Mendaftar');                        
                    }
                }
            } else {
                $status = $sms->setStatus($id, 8);
                exit('[ '.date('H:i:s').' ] Gagal Mengirim OTP Daftar');
            }
        }
    } else {
        die($getnum);
    }
}

function input($text) {
    echo $text.": ";
    $a = trim(fgets(STDIN));
    return $a;
}

function RandomDeviceToken($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstvwxyzABCDEFGHIJKLMNOPQRSTVWXYZ-_:';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function RandomUUID($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
