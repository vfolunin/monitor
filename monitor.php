<?php

function file_get_contents_curl($url, $cookie = "") {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array($cookie));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function getAcmpProblems($id) {
    $problems = array();
    for ($page = 0; ; $page++) {
        $contents = file_get_contents_curl("http://acmp.ru/index.asp?main=status&id_mem=$id&id_res=1&page=$page");
        preg_match_all("#\<td\>\<a href=[\/\S]*?\?main=task[\/\S]*?\>[0]*([0-9]+?)\<\/a\>#", $contents, $match);
        if (empty($match[1]))
            break;
        foreach($match[1] as $problem)
            $problems[] = $problem;
    }
    return array_unique($problems);
}

$MCCME_USERNAME = "username";
$MCCME_PASSWORD = "password";

function getMccmeCookie() {
    global $MCCME_USERNAME, $MCCME_PASSWORD;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://informatics.msk.ru/login/index.php");
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);

    preg_match_all("#logintoken\" value=\"(\S*)\"#", $response, $match);
    $token = $match[1][0];
    preg_match_all("#Set-Cookie: ([\S]+);#", $response, $match);
    $cookie = "Cookie:" . $match[1][0];

    curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", $cookie));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "logintoken=$token&username=$MCCME_USERNAME&password=$MCCME_PASSWORD");
    $response = curl_exec($ch);

    preg_match_all("#Set-Cookie: ([\S]+);#", $response, $match);
    $cookie = "Cookie:" . $match[1][0];

    curl_close($ch);
    return $cookie;
}

function getMccmeProblems($id) {
    static $cookie;
    if ($cookie == "")
        $cookie = getMccmeCookie();
    $problems = array();
    foreach (array(0, 8) as $status_id) {
        $contents = json_decode(file_get_contents_curl("https://informatics.msk.ru/py/problem/0/filter-runs?user_id=$id&status_id=$status_id&count=50&page=1", $cookie), true);
        foreach($contents["data"] as $problem)
            $problems[] = $problem["problem"]["id"];
        $pages = $contents["metadata"]["page_count"];
        for ($page = 2; $page <= $pages; $page++) {
            $contents = json_decode(file_get_contents_curl("https://informatics.msk.ru/py/problem/0/filter-runs?user_id=$id&status_id=$status_id&count=50&page=$page", $cookie), true);
            if (isset($contents["data"]))
                foreach ($contents["data"] as $problem)
                    $problems[] = $problem["problem"]["id"];
        }
    }
    return array_unique($problems);
}

function getTimusProblems($id) {
    $contents = file_get_contents_curl("http://acm.timus.ru/author.aspx?id=$id");
    preg_match_all("#\<TD CLASS=\"accepted\"\>\<A[\s\S]*?>([\d]+?)\</A\>\</TD\>#", $contents, $match);
    return array_values($match[1]);
}

function getCodeforcesProblems($id) {
    $apiResponse = json_decode(file_get_contents_curl("https://codeforces.com/api/user.status?handle=$id&from=1&count=1000000000"), true);
    $problems = array();
    if (isset($apiResponse["result"])) {
        foreach ($apiResponse["result"] as $submission) {
            if (!strcmp($submission["verdict"], "OK")) {
                if (isset($submission["problem"]["problemsetName"]) && !strcmp($submission["problem"]["problemsetName"], "acmsguru"))
                    $problems[] = "sgu." . $submission["problem"]["index"];
                else if (isset($submission["problem"]["contestId"]) && isset($submission["problem"]["index"]))
                    $problems[] = $submission["problem"]["contestId"] . "." . $submission["problem"]["index"];
            }
        }
    }
    return array_unique($problems);
}

function getEolympProblems($id) {
    $contents = file_get_contents_curl("http://www.e-olymp.com/ru/users/$id/punchcard");
    preg_match_all("#([\d]+)\" class=\"eo-punchcard__cell eo-punchcard__cell_active\"#", $contents, $match);
    return array_values($match[1]);
}

function getSpojProblems($id) {
    $contents = file_get_contents_curl("http://www.spoj.com/users/$id");
    preg_match_all("#solved classical[\s\S]*?<table.*>([\s\S]+?)<\/table>#", $contents, $match);
    preg_match_all("#status\/(.+?),#", $match[1][0], $match);
    return array_values($match[1]);
}

function getUvaProblemMap() {
    $apiResponse = json_decode(file_get_contents_curl("https://uhunt.onlinejudge.org/api/p"), true);
    $problemMap = array();
    foreach ($apiResponse as $problem)
        $problemMap[$problem[0]] = $problem[1];
    return $problemMap;
}

function getUvaProblems($id) {
    static $problemMap;
    if (empty($problemMap))
        $problemMap = getUvaProblemMap();
    $apiResponse = json_decode(file_get_contents_curl("https://uhunt.onlinejudge.org/api/subs-user/$id"), true);
    $problems = array();
    if (isset($apiResponse["subs"]))
        foreach ($apiResponse["subs"] as $submission)
            if ($submission[2] == 90)
                $problems[] = $problemMap[$submission[1]];
    return array_unique($problems);
}

$prefixes = array("",
    "acmp_",
    "mccme_",
    "timus_",
    "cf_",
    "eolymp_",
    "spoj_",
    "uva_"
);

$getFunctions = array("",
    "getAcmpProblems",
    "getMccmeProblems",
    "getTimusProblems",
    "getCodeforcesProblems",
    "getEolympProblems",
    "getSpojProblems",
    "getUvaProblems"
);

function jsStats($users, $file) {
    global $prefixes, $getFunctions;
    foreach ($users as $user) {
        $ids = array();
        $problems = array();
        for ($i = 1; $i < count($user); $i++) {
            if ($user[$i] == "")
                continue;
            $ids[] = "\"" . $prefixes[$i] . $user[$i] . "\"";
            $problems = array_merge($problems, array_map(create_function('$a', 'return "\"' . $prefixes[$i] . '" . $a . "\"";'), call_user_func($getFunctions[$i], $user[$i])));
        }
        $js[] = "{userName: \"" . $user[0] . "\", ids: [" . implode($ids, ", ") . "], problems: [" . implode($problems, ", ") . "]}";
    }
    file_put_contents($file, "stats = [" . implode($js, ",\n") . "];");
}

include("users.php");

jsStats($users, "stats.js");

?>