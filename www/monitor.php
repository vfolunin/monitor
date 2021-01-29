<?php

function file_get_contents_curl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_AUTOREFERER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function getAcmpProblems($id) {
    $problems = array();
    for ($page = 0; ; $page++) {
        $contents = file_get_contents_curl("http://acmp.ru/index.asp?main=status&id_mem=" . $id . "&id_res=1&page=" . $page);
        preg_match_all("#\<td\>\<a href=[\/\S]*?\?main=task[\/\S]*?\>[0]*([0-9]+?)\<\/a\>#", $contents, $match);
        if (empty($match[1]))
            break;
        foreach($match[1] as $problem)
            $problems[] = $problem;
    }
    return array_unique($problems);
}

function getTimusProblems($id) {
    $contents = file_get_contents_curl("http://acm.timus.ru/author.aspx?id=" . $id);
    preg_match_all("#\<TD CLASS=\"accepted\"\>\<A[\s\S]*?>([\d]+?)\</A\>\</TD\>#", $contents, $match);
    return array_values($match[1]);
}

function getCodeforcesProblems($id) {
    $apiResponse = json_decode(file_get_contents_curl("https://codeforces.com/api/user.status?handle=" . $id . "&from=1&count=1000000000"), true);
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
    $contents = file_get_contents_curl("http://www.e-olymp.com/ru/users/" . $id . "/punchcard");
    preg_match_all("#([\d]+)\" class=\"eo-punchcard__cell eo-punchcard__cell_active\"#", $contents, $match);
    return array_values($match[1]);
}

function getSpojProblems($id) {
    $contents = file_get_contents_curl("http://www.spoj.com/users/" . $id);
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
    $apiResponse = json_decode(file_get_contents_curl("https://uhunt.onlinejudge.org/api/subs-user/" . $id), true);
    $problems = array();
    if (isset($apiResponse["subs"]))
        foreach ($apiResponse["subs"] as $submission)
            if ($submission[2] == 90)
                $problems[] = $problemMap[$submission[1]];
    return array_unique($problems);
}

$prefixes = array("",
    "acmp_",
    "timus_",
    "cf_",
    "eolymp_",
    "spoj_",
    "uva_"
);

$getFunctions = array("",
    "getAcmpProblems",
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