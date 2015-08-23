<?php

function getAcmpProblems($id) {
    $contents = file_get_contents("http://acmp.ru/?main=user&id=" . $id);
    preg_match_all("#\<p class=text\>([\s\S]*?)\</p\>#", $contents, $match);
    preg_match_all("#\<a href=\?main=task\&id_task=[\d]+?>([\d]+?)\</a\>#", $match[1][0], $match);
    return array_values($match[1]);
}

function getTimusProblems($id) {
    $contents = file_get_contents("http://acm.timus.ru/author.aspx?id=" . $id);
    preg_match_all("#\<TD CLASS=\"accepted\"\>\<A[\s\S]*?>([\d]+?)\</A\>\</TD\>#", $contents, $match);
    return array_values($match[1]);
}

function getSguProblems($id) {
    $contents = file_get_contents("http://acm.sgu.ru/teaminfo.php?id=" . $id);
    preg_match_all("#\<tr\>\<td\>Accepted\</td\>([\s\S]*?)\</tr\>#", $contents, $match);
    preg_match_all("#\<font[\s\S]*?\>([\d]+?)\&#", $match[1][0], $match);
    return array_values($match[1]);
}

function getMccmeProblems($id) {
    $userInfo = file_get_contents("http://informatics.mccme.ru/moodle/user/view.php?id=" . $id);
    preg_match_all("#\<title\>[\s\S]*?:[\s\S]*?\</title\>#", $userInfo, $match);
    if (count($match[0]) == 0)
        return array();
    $json = json_decode(file_get_contents("http://informatics.mccme.ru/moodle/ajax/ajax.php?lang_id=-1&status_id=0&objectName=submits&count=1000000000&action=getHTMLTable&user_id=" . $id));
    $contents = $json->result->text;
    preg_match_all("#\<a href=\"/moodle/mod/statements[\s\S]*?\>([\d]+?)\.#", $contents, $match);
    return array_values(array_unique($match[1]));
}

function getCodeforcesProblems($id) {
    $apiResponse = json_decode(file_get_contents("http://codeforces.ru/api/user.status?handle=" . $id . "&from=1&count=1000000000"), true);
    $problems = array();
    foreach ($apiResponse["result"] as $submission) {
        if (!strcmp($submission["verdict"], "OK"))
            $problems[] = $submission["problem"]["contestId"] . "." . $submission["problem"]["index"];
    }
    return array_unique($problems);
}

function getEolympProblems($id) {
    $contents = file_get_contents("http://www.e-olymp.com/ru/users/" . $id . "/punchcard");
    preg_match_all("#([\d]+)\" class=\"eo-punchcard__cell eo-punchcard__cell_active\"#", $contents, $match);
    return array_values($match[1]);
}

$prefixes = array("", "acmp_", "timus_", "sgu_", "mccme_", "cf_", "eolymp_");
$getFunctions = array("", "getAcmpProblems", "getTimusProblems", "getSguProblems", "getMccmeProblems", "getCodeforcesProblems", "getEolympProblems");

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