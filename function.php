<?php

function beep()
{
    fprintf(STDOUT, "%s", "\x07");
}

function clear()
{
  return print(chr(27) . chr(91) . 'H' . chr(27) . chr(91) . 'J'."\n"); //^[H^[J
}

function exp_card_format($ccxpm, $ccxpy, $type)
{
    if($type == 1)
    {
        $ccxpm = strlen($ccxpm) == 2 ? $ccxpm : "0".$ccxpm;
        $ccxpy = strlen($ccxpy) == 4 ? substr($ccxpy, 2) : $ccxpy;
    }elseif($type == 2)
    {
        $ccxpm = strlen($ccxpm) == 2 ? ($ccxpm[0] == 0 ? substr($ccxpm, -1) : $ccxpm) : $ccxpm;
        $ccxpy = strlen($ccxpy) == 2 ? "20".$ccxpy : $ccxpy;
    }
    return [$ccxpm, $ccxpy];
}

function exp_card_check($ccxpm, $ccxpy)
{
    if(strtotime("{$ccxpy}-{$ccxpm}-01") < strtotime(date("Y-n")."-01"))
    {
        print(colored_string("Card is expired\n", "red"));
        return false;
    }elseif(strtotime("{$ccxpy}-{$ccxpm}-01") < strtotime(date("y-m")."-01"))
    {
        print(colored_string("Card is expired\n", "red"));
        return false;
    }
}

function multi_explode($delimiters, $string)
{
    $firstChar = str_replace($delimiters, $delimiters[0], $string);
    $delim = explode($delimiters[0], $firstChar);
    return $delim;
}

function get_string_between($string, $start, $end){
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}

function get_fake_info()
{
    chkulang:
    $site = cURL("https://randomuser.me/api/?nat=us");
    if(strpos($site, 'Uh oh, something bad happened') or strpos($site, 'cloudflare'))
    {
        goto chkulang;
    }elseif(strlen($site)>1)
    {
        return json_decode($site, true)['results'][0];
    }else
    {
        goto chkulang;
    }
}

function get_bin($bin_code)
{
    chkulang:
    $time = time();
    $gen = json_decode(cURL("https://dnschecker.org/ajax_files/gen_csrf.php?upd=".substr($time, 0,3).".".substr($time, -13), "", ['User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:105.0) Gecko/20100101 Firefox/105.0', 'Accept: application/json, text/javascript, */*; q=0.01', 'Accept-Language: en-US,en;q=0.5', 'Csrftoken: null', 'X-Requested-With: XMLHttpRequest', 'Origin: https://dnschecker.org', 'Referer: https://dnschecker.org/bin-checker.phpn', 'Content-Length: 0']), true);
    if(!isset($gen['csrf'])) goto chkulang;
    $web = json_decode(cURL("https://dnschecker.org/ajax_files/bin_checker.php?bin=".$bin_code, false, ['User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:105.0) Gecko/20100101 Firefox/105.0', 'Accept: */*', 'Accept-Language: en-US,en;q=0.5', 'Referer: https://dnschecker.org/bin-checker.php', 'Csrftoken: '.$gen['csrf'], 'Pragma: no-cache', 'Cache-Control: no-cache']), true);
    if(isset($web['results']['bank']))
    {
        return "[BIN: $bin_code / " . (!empty($web['results']['network']) ? trim(strtoupper($web['results']['network'])) : "N/A") . " - " . (!empty($web['results']['card_type']) ? trim(strtoupper($web['results']['card_type'])) : "N/A") . " - " . (!empty($web['results']['bank']) ? trim(strtoupper($web['results']['bank'])) : "N/A") . " - " . (!empty($web['results']['country_data']['iso']) ? trim(strtoupper($web['results']['country_data']['iso'])) : "N/A") . "]";
    }else
    {
        goto chkulang;
    }
}

function random_string($length, $type = null)
{
    // @see http://stackoverflow.com/a/853846/11301
    switch($type)
    {
        case '1':
            $alphanum='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
            break;
        case '2':
            $alphanum='ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            break;
        case '3':
            $alphanum='abcdefghijklmnopqrstuvwxyz0123456789';
            break;
        case '4':
            $alphanum='ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            break;
        default:
            $alphanum='abcdefghijklmnopqrstuvwxyz';
    }
    return substr(str_shuffle(str_repeat($alphanum, $length)), 0, $length);
}

function coloredStr($string, $color)
{
    $array = [
        "green" => "1;32",
        "red" => "1;31",
        "yellow" => "1;33",
        "purple" => "1;35",
        "cyan" => "1;36",
        "gray" => "0;37",
    ];
    $text = "\033[" . $array[$color] . "m" . $string . "\033[0m";
    return $text;
}

function cURL($cUrl, $cBody = false, $cHttpHeaders = false, $cHeader = false, $cCookies = false, $cUserPwd = false)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $cUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

    if ($cHttpHeaders and $cHttpHeaders != false) curl_setopt($ch, CURLOPT_HTTPHEADER, $cHttpHeaders);
    if ($cHeader and $cHeader != false) curl_setopt($ch, CURLOPT_HEADER, 1);
    if ($cUserPwd and $cUserPwd != false) curl_setopt($ch, CURLOPT_USERPWD, $cUserPwd);

    if ($cBody and $cBody != false):
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $cBody);
    else:
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    endif;

    if ($cCookies and $cCookies != false):
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cCookies);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cCookies);
    endif;

    $result = curl_exec($ch);
    return $result; //if(!$result || strlen(trim($result)) == 0)
    curl_close($ch);
}