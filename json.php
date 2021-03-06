<?php
error_reporting(E_ALL & ~E_NOTICE);
$write_cache = 0;
$epoch = date('U');
$random_bla = md5(uniqid(rand(), true));
foreach (glob("functions/*.php") as $filename) {
  include $filename;
}

if ( isset($_GET['host']) && !empty($_GET['host'])) {
  $data = [];
  $hostname = mb_strtolower(get($_GET['host']));
  $hostname = parse_hostname($hostname);
  if ($hostname['multiple_ip']) {
	  $hostname['ip'] = $hostname['multiple_ip'][0]['ip'];
  } 
  $host = $hostname['hostname'];
  $ip = $hostname['ip'];
  $port = get($_GET['port'], '443');
  if ( !is_numeric($port) ) {
    $port = 443;
  }
  $fastcheck = $_GET['fastcheck'];
  $write_cache = 1;
  $hostfilename = preg_replace("([^\w\s\d\-_~,;:\[\]\(\).])", '', $host);
  $hostfilename = preg_replace("([\.]{2,})", '', $host);
  $hostfilename = preg_replace("([^a-z0-9])", '', $host);
  $cache_filename = (string) "results/saved." . $hostfilename . "." . $epoch . "." . $random_bla . ".api.json";
  $data["data"] = check_json($host, $ip, $port, $fastcheck);
} elseif(isset($_GET['csr']) && !empty($_GET['csr'])) {
  $write_cache = 1;
  $cache_filename = (string) "results/saved.csr." . $epoch . "." . $random_bla . ".api.json";
  $data["data"]["chain"]["1"] = csr_parse_json($_GET['csr']);
} else {
  $data["error"] = ["Host is required"];
}

$data['version'] = $version;
$data = utf8encodeNestedArray($data);

if(isset($data["data"]["error"])) {
  $data["error"] = $data["data"]["error"];
  unset($data["data"]);
}

  header('Content-Type: application/json');
  echo json_encode($data);


if ($write_cache == 1) {
  if (!file_exists($cache_filename)) {
   //file_put_contents($cache_filename, json_encode($data));
  }
}

?>

