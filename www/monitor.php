<?php

function addQuot($array) {
    for ($i = 0; $i < count($array); $i++)
        $array[$i] = "\"" . $array[$i] . "\"";
    return $array;
}

function getAcmpProblems($id) {
    $contents = file_get_contents("http://acmp.ru/?main=user&id=" . $id);
    preg_match_all("#\<p class=text\>([\s\S]*?)\</p\>#", $contents, $match);
    preg_match_all("#\<a href=\?main=task\&id_task=[\d]+?>([\d]+?)\</a\>#", $match[1][0], $match);
    return addQuot(array_values($match[1]));
}

function getTimusProblems($id) {
    $contents = file_get_contents("http://acm.timus.ru/author.aspx?id=" . $id);
    preg_match_all("#\<TD CLASS=\"accepted\"\>\<A[\s\S]*?>([\d]+?)\</A\>\</TD\>#", $contents, $match);
    return addQuot(array_values($match[1]));
}

function getSguProblems($id) {
    $contents = file_get_contents("http://acm.sgu.ru/teaminfo.php?id=" . $id);
    preg_match_all("#\<tr\>\<td\>Accepted\</td\>([\s\S]*?)\</tr\>#", $contents, $match);
    preg_match_all("#\<font[\s\S]*?\>([\d]+?)\&#", $match[1][0], $match);
    return addQuot(array_values($match[1]));
}

function getMccmeProblems($id) {
    $userInfo = file_get_contents("http://informatics.mccme.ru/moodle/user/view.php?id=" . $id);
    preg_match_all("#\<title\>[\s\S]*?:[\s\S]*?\</title\>#", $userInfo, $match);
    if (count($match[0]) == 0)
        return array();
    $json = json_decode(file_get_contents("http://informatics.mccme.ru/moodle/ajax/ajax.php?lang_id=-1&status_id=0&objectName=submits&count=1000000000&action=getHTMLTable&user_id=" . $id));
    $contents = $json->result->text;
    preg_match_all("#\<a href=\"/moodle/mod/statements[\s\S]*?\>([\d]+?)\.#", $contents, $match);
    asort($match[1]);
    return addQuot(array_values($match[1]));
}

function getCodeforcesProblems($id) {
    $userInfo = file_get_contents("http://codeforces.ru/submissions/" . (strcmp($id, "") ? $id : " ") . "/page/100000000");
    preg_match_all("#\<title\>([\s\S]*?-[\s\S]*?)\</title\>#", $userInfo, $match);
    if (count($match[1]) == 0)
        return array();
    preg_match_all("#\<span class=\"page-index active\" pageIndex=\"([\d]+?)\"\>#", $userInfo, $match);
    $pageCnt = count($match[1]) ? $match[1][0] : 1;
    $contents = "";
    for ($i = 0; $i < $pageCnt; $i++)
        $contents .= file_get_contents("http://codeforces.ru/submissions/" . (strcmp($id, "") ? $id : " ") . "/page/" . ($i + 1));
    preg_match_all("#\<tr[\s\S]*?data-submission-id=\"[\d]+?\"\>([\s\S]*?)\</tr\>#", $contents, $match);
    $contents = implode("@", $match[1]);
    preg_match_all("#\"/problemset/([0-9a-zA-Z/]*?)\"\>[\s]*([0-9a-zA-Z]*) -[^@]*\<span class='verdict-accepted'\>#", $contents, $match);
    natsort($match[2]);
    return addQuot(array_values(array_unique($match[2])));
}

function jsStats($users, $file) {
    foreach ($users as $value) {
        $stats = array(
            "{userId: \"" . $value[1] . "\", problems: [" . implode(getAcmpProblems($value[1]), ", ") . "]}",
            "{userId: \"" . $value[2] . "\", problems: [" . implode(getTimusProblems($value[2]), ", ") . "]}",
            "{userId: \"" . $value[3] . "\", problems: [" . implode(getSguProblems($value[3]), ", ") . "]}",
            "{userId: \"" . $value[4] . "\", problems: [" . implode(getMccmeProblems($value[4]), ", ") . "]}",
            "{userId: \"" . $value[5] . "\", problems: [" . implode(getCodeforcesProblems($value[5]), ", ") . "]}"
        );
        $js[] = "{userName: \"" . $value[0] . "\", stats: [" . implode($stats, ", ") . "]}";
    }
    file_put_contents($file, "stats = [" . implode($js, ",\n") . "];");
}

include("users.php");

jsStats($users, "stats.js");

?>