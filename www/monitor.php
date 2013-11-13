<?php

function getStats($siteName, $siteUrl, $problems) {
    $stats = "<h2>Statistics for <a href=\"" . $siteUrl . "\">" . $siteName . "</a></h2>";
    if (($prCnt = count($problems[1])) == 0)
        return $stats .= "<h3>Problems solved: 0</h3>";
    $stats .= "<h3>Problems solved: <a href=\"" . $problems[0] . "\">" . $prCnt . "</a></h3>";
    $stats .= "<table>";
    $i = 0;
    foreach ($problems[1] as $key => $value) {
        if ($i % 20 == 0)
            $stats .= "<tr>";
        $stats .= "<td><a href=\"" . $value . "\">" . $key . "</a></td>";
        if ($i % 20 == 19)
            $stats .= "</tr>";
        $i++;
    }
    $stats .= "</table>";
    return $stats;
}

function mark1($str) {
    return "<span style=\"background-color:#99f\">" .$str . "</span>";
}
function mark2($str) {
    return "<span style=\"background-color:#f99\">" .$str . "</span>";
}
function getCmpStats($siteName, $siteUrl, $problemsA, $problemsB) {
    $stats = "<h2>Comparable Statistics for <a href=\"" . $siteUrl . "\">" . $siteName . "</a></h2>";
    $prCntA = count($problemsA[1]);
    $prCntB = count($problemsB[1]);
    $stats .= "<h3>Problems solved: " . mark1($prCntA ? "<a href=\"" . $problemsA[0] . "\">" . $prCntA . "</a>" : "0") . " / " . mark2($prCntB ? "<a href=\"" . $problemsB[0] . "\">" . $prCntB . "</a>" : "0") . "</h3>";
    $stats .= "<table>";
    $keysA = array_keys($problemsA[1]);
    $keysB = array_keys($problemsB[1]);
    for ($i = 0, $j = 0, $k = 0; $i < $prCntA || $j < $prCntB; $k++) {
        if ($k % 20 == 0)
            $stats .= "<tr>";
        if ($i < $prCntA && ($j >= $prCntB || strnatcmp($keysA[$i], $keysB[$j]) < 0)) {
            $stats .= "<td>" . mark1("<a href=\"" . $problemsA[1][$keysA[$i]] . "\">" . $keysA[$i] . "</a>") . "</td>";
            $i++;
        } else if ($j < $prCntB && ($i >= $prCntA || strnatcmp($keysA[$i], $keysB[$j]) > 0)) {
            $stats .= "<td>" . mark2("<a href=\"" . $problemsB[1][$keysB[$j]] . "\">" . $keysB[$j] . "</a>") . "</td>";
            $j++;
        } else {
            $stats .= "<td><a href=\"" . $problemsA[1][$keysA[$i]] . "\">" . $keysA[$i] . "</a></td>";
            $i++;
            $j++;
        }
        if ($k % 20 == 19)
            $stats .= "</tr>";
    }
    $stats .= "</table>";
    return $stats;
}

function getAcmpProblems($id) {
    $userPage = "http://acmp.ru/?main=user&id=" . $id;
    $problems = array();
    $contents = file_get_contents($userPage);
    preg_match_all("#\<p class=text\>([\s\S]*?)\</p\>#", $contents, $match);
    preg_match_all("#\<a href=\?main=task\&id_task=[\d]+?>([\d]+?)\</a\>#", $match[1][0], $match);
    foreach ($match[1] as $key => $value)
        $problems[$value] = "http://acmp.ru/?main=task&id_task=" . $value;
    return array($userPage, $problems);
}

function getTimusProblems($id) {
    $userPage = "http://acm.timus.ru/author.aspx?id=" . $id;
    $problems = array();
    $contents = file_get_contents($userPage);
    preg_match_all("#\<TD CLASS=\"accepted\"\>\<A[\s\S]*?>([\d]+?)\</A\>\</TD\>#", $contents, $match);
    foreach ($match[1] as $key => $value)
        $problems[$value] = "http://acm.timus.ru/problem.aspx?space=1&num=" . $value;
    return array($userPage, $problems);
}

function getSguProblems($id) {
    $userPage = "http://acm.sgu.ru/teaminfo.php?id=" . $id;
    $problems = array();
    $contents = file_get_contents($userPage);
    preg_match_all("#\<tr\>\<td\>Accepted\</td\>([\s\S]*?)\</tr\>#", $contents, $match);
    preg_match_all("#\<font[\s\S]*?\>([\d]+?)\&#", $match[1][0], $match);
    foreach ($match[1] as $key => $value)
        $problems[$value] = "http://acm.sgu.ru/problem.php?contest=0&problem=" . $value;
    return array($userPage, $problems);
}

function getMccmeProblems($id) {
    $userPage = "http://informatics.mccme.ru/moodle/submits/view.php?user_id=" . $id;
    $problems = array();
    $userInfo = file_get_contents("http://informatics.mccme.ru/moodle/user/view.php?id=" . $id);
    preg_match_all("#\<title\>[\s\S]*?:[\s\S]*?\</title\>#", $userInfo, $match);
    if (count($match[0]) == 0)
        return array($userPage, $problems);
    $json = json_decode(file_get_contents("http://informatics.mccme.ru/moodle/ajax/ajax.php?lang_id=-1&status_id=0&objectName=submits&count=1000000000&action=getHTMLTable&user_id=" . $id));
    $contents = $json->result->text;
    preg_match_all("#\<a href=\"/moodle/mod/statements[\s\S]*?\>([\d]+?)\.#", $contents, $match);
    asort($match[1]);
    foreach ($match[1] as $key => $value)
        $problems[$value] = "http://informatics.mccme.ru/moodle/mod/statements/view3.php?chapterid=" . $value;
    return array($userPage, $problems);
}

function getCodeforcesProblems($id) {
    if ($id == "")
        $id = " ";
    $userPage = "http://codeforces.ru/profile/" . $id;
    $problems = array();
    $userInfo = file_get_contents("http://codeforces.ru/submissions/" . $id . "/page/100000000");
    preg_match_all("#\<title\>([\s\S]*?-[\s\S]*?)\</title\>#", $userInfo, $match);
    if (count($match[1]) == 0)
        return array($userPage, $problems);
    preg_match_all("#\<span class=\"page-index active\" pageIndex=\"([\d]+?)\"\>#", $userInfo, $match);
    $pageCnt = count($match[1]) ? $match[1][0] : 1;
    $contents = "";
    for ($i = 0; $i < $pageCnt; $i++)
        $contents .= file_get_contents("http://codeforces.ru/submissions/" . $id . "/page/" . ($i + 1));
    preg_match_all("#\<tr[\s\S]*?data-submission-id=\"[\d]+?\"\>([\s\S]*?)\</tr\>#", $contents, $match);
    $contents = implode("@", $match[1]);
    preg_match_all("#\"/problemset/([0-9a-zA-Z/]*?)\"\>[\s]*([0-9a-zA-Z]*) -[^@]*\<span class='verdict-accepted'\>#", $contents, $match);
    natsort($match[2]);
    foreach ($match[2] as $value) {
        $problems[$value] = "http://codeforces.ru/problemset/" . (!strcmp(substr($value, 0, 3), "100") ? "gymProblem/" : "problem/");
        preg_match_all("#([\d]+)([\s\S]*)#", $value, $vMatch);
        $problems[$value].= $vMatch[1][0] . "/" . $vMatch[2][0];
    }
    return array($userPage, $problems);
}

function jsProblems($data) {
    $pjs = array();
    foreach ($data[1] as $key => $value)
        $pjs[] = "{id:\"" . $key . "\", url:\"" . $value . "\"}";
    return "{userPage:\"" . $data[0] . "\", problems:[" . implode($pjs, ", ") . "]}";
}

function jsStats($users, $file) {
    $js = array();
    foreach ($users as $value) {
        $stats = array(
            jsProblems(getAcmpProblems($value[1])),
            jsProblems(getTimusProblems($value[2])),
            jsProblems(getSguProblems($value[3])),
            jsProblems(getMccmeProblems($value[4])),
            jsProblems(getCodeforcesProblems($value[5]))
        );
        $js[] = "{userName:\"" . $value[0] . "\", stats:[" . implode($stats, ", ") . "]}";
    }
    file_put_contents($file, "stats = [" . implode($js, ", ") . "];");
}

include("users.php");

jsStats($users, "stats.js");

/*
echo(getStats("ACMP", "http://acmp.ru/", getAcmpProblems( mysql_escape_string($_POST["idAcmp"]) )));
echo(getStats("Timus Online Judge", "http://acm.timus.ru/", getTimusProblems( mysql_escape_string($_POST["idTimus"]) )));
echo(getStats("Saratov SU Online Contester", "http://acm.sgu.ru/", getSguProblems( mysql_escape_string($_POST["idSgu"]) )));
echo(getStats("MCCME", "http://informatics.mccme.ru/", getMccmeProblems( mysql_escape_string($_POST["idMccme"]) )));
echo(getStats("Codeforces", "http://codeforces.ru/", getCodeforcesProblems( mysql_escape_string($_POST["idCodeforces"]) )));
*/
/*
echo(getCmpStats("ACMP", "http://acmp.ru/", getAcmpProblems( mysql_escape_string($_POST["idAcmp"]) ), getAcmpProblems( mysql_escape_string($_POST["idCmpAcmp"]) )));
echo(getCmpStats("Timus Online Judge", "http://acm.timus.ru/", getTimusProblems( mysql_escape_string($_POST["idTimus"]) ), getTimusProblems( mysql_escape_string($_POST["idCmpTimus"]) )));
echo(getCmpStats("Saratov SU Online Contester", "http://acm.sgu.ru/", getSguProblems( mysql_escape_string($_POST["idSgu"]) ), getSguProblems( mysql_escape_string($_POST["idCmpSgu"]) )));
echo(getCmpStats("MCCME", "http://informatics.mccme.ru/", getMccmeProblems( mysql_escape_string($_POST["idMccme"]) ), getMccmeProblems( mysql_escape_string($_POST["idCmpMccme"]) )));
echo(getCmpStats("Codeforces", "http://codeforces.ru/", getCodeforcesProblems( mysql_escape_string($_POST["idCodeforces"]) ), getCodeforcesProblems( mysql_escape_string($_POST["idCmpCodeforces"]) )));
*/

?>