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
    /*
    $contents = file_get_contents_curl("http://acmp.ru/?main=user&id=" . $id);
    preg_match_all("#\<p class=text\>([\s\S]*?)\</p\>#", $contents, $match);
    preg_match_all("#\<a href=\?main=task\&id_task=[\d]+?>([\d]+?)\</a\>#", $match[1][0], $match);
    return array_values($match[1]);
    */
}

function getTimusProblems($id) {
    $contents = file_get_contents_curl("http://acm.timus.ru/author.aspx?id=" . $id);
    preg_match_all("#\<TD CLASS=\"accepted\"\>\<A[\s\S]*?>([\d]+?)\</A\>\</TD\>#", $contents, $match);
    return array_values($match[1]);
}

function getSguProblems($id) {
    $contents = file_get_contents_curl("http://acm.sgu.ru/teaminfo.php?id=" . $id);
    preg_match_all("#\<tr\>\<td\>Accepted\</td\>([\s\S]*?)\</tr\>#", $contents, $match);
    preg_match_all("#\<font[\s\S]*?\>([\d]+?)\&#", $match[1][0], $match);
    return array_values($match[1]);
}

function getMccmeProblems($id) {
    $problems = array();
    for ($page = 0; ; $page++) {
        $contents = file_get_contents_curl("http://informatics.mccme.ru/moodle/ajax/ajax.php?problem_id=0&user_id=" . $id . "&lang_id=-1&status_id=0&objectName=submits&count=100&with_comment=&page=" . $page . "&action=getHTMLTable");
        preg_match_all("#chapterid=([\d]+)#", $contents, $match);
        if (empty($match[1]))
            break;
        foreach($match[1] as $problem)
            $problems[] = $problem;
    }
    for ($page = 0; ; $page++) {
        $contents = file_get_contents_curl("http://informatics.mccme.ru/moodle/ajax/ajax.php?problem_id=0&user_id=" . $id . "&lang_id=-1&status_id=8&objectName=submits&count=100&with_comment=&page=" . $page . "&action=getHTMLTable");
        preg_match_all("#chapterid=([\d]+)#", $contents, $match);
        if (empty($match[1]))
            break;
        foreach($match[1] as $problem)
            $problems[] = $problem;
    }
    return array_unique($problems);
}

function getCodeforcesProblems($id) {
    $apiResponse = json_decode(file_get_contents_curl("http://codeforces.ru/api/user.status?handle=" . $id . "&from=1&count=1000000000"), true);
    $problems = array();
    foreach ($apiResponse["result"] as $submission) {
        if (!strcmp($submission["verdict"], "OK"))
            $problems[] = $submission["problem"]["contestId"] . "." . $submission["problem"]["index"];
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

function getHackerearthProblems($id) {
    $contents = file_get_contents_curl("https://www.hackerearth.com/users/pagelets/" . $id . "/solved-practice-problems");
    preg_match_all("#algorithm\/(.+?)\/#", $contents, $match);
    return array_values($match[1]);
}

$prefixes = array("",
    "acmp_",
    "timus_",
    "sgu_",
    "mccme_",
    "cf_",
    "eolymp_",
    "spoj_",
    "hackerearth_"
);

$getFunctions = array("",
    "getAcmpProblems",
    "getTimusProblems",
    "getSguProblems",
    "getMccmeProblems",
    "getCodeforcesProblems",
    "getEolympProblems",
    "getSpojProblems",
    "getHackerearthProblems"
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