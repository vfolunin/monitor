
var sites = [
    {
        name : "ACMP",
        prefix : "acmp_",
        url : "http://acmp.ru/",
        userUrl : function(uid) {
            return this.url + "?main=user&id=" + uid;
        },
        problemUrl : function(pid) {
            return this.url + "?main=task&id_task=" + pid;
        }
    },
    {
        name : "Timus Online Judge",
        prefix : "timus_",
        url : "http://acm.timus.ru/",
        userUrl : function(uid) {
            return this.url + "author.aspx?id=" + uid;
        },
        problemUrl : function(pid) {
            return this.url + "problem.aspx?num=" + pid;
        }
    },
    {
        name : "СГУ",
        prefix : "sgu_",
        url : "http://acm.sgu.ru/",
        userUrl : function(uid) {
            return this.url + "teaminfo.php?id=" + uid;
        },
        problemUrl : function(pid) {
            return this.url + "problem.php?problem=" + pid;
        }
    },
    {
        name : "МЦНМО",
        prefix : "mccme_",
        url : "http://informatics.mccme.ru/",
        userUrl : function(uid) {
            return this.url + "moodle/submits/view.php?user_id=" + uid;
        },
        problemUrl : function(pid) {
            return this.url + "moodle/mod/statements/view3.php?chapterid=" + pid;
        }
    },
    {
        name : "Codeforces",
        prefix : "cf_",
        url : "http://codeforces.com/",
        userUrl : function(uid) {
            return this.url + "profile/" + uid;
        },
        problemUrl : function(pid) {
            var dotPos = pid.indexOf('.');
            var folder = dotPos < 6 ? "contest/" : "gym/";
            return this.url + folder + pid.slice(0, dotPos) + "/problem/" + pid.slice(dotPos + 1);
        }
    },
    {
        name : "E-olymp",
        prefix : "eolymp_",
        url : "http://www.e-olymp.com/",
        userUrl : function(uid) {
            return this.url + "ru/users/" + uid;
        },
        problemUrl : function(pid) {
            return this.url + "ru/problems/" + pid;
        }
    },
    {
        name : "SPOJ",
        prefix : "spoj_",
        url : "http://www.spoj.com/",
        userUrl : function(uid) {
            return this.url + "users/" + uid;
        },
        problemUrl : function(pid) {
            return this.url + "problems/" + pid;
        }
    },
    {
        name : "HackerEarth",
        prefix : "hackerearth_",
        url : "http://www.hackerearth.com/",
        userUrl : function(uid) {
            return this.url + "@" + uid + "/activity/hackerearth";
        },
        problemUrl : function(pid) {
            return this.url + "problem/algorithm/" + pid;
        }
    }
];

var users = [];
for (var userNo = 0; userNo < stats.length; userNo++) {
    var user = {};
    user.userNo = userNo;
    user.name = stats[userNo].userName;
    user.total = stats[userNo].problems.length;
    user.problemsCnt = [];
    for (var siteNo = 0; siteNo < sites.length; siteNo++)
        user.problemsCnt.push(filterProblems(userNo, siteNo).length);
    users.push(user);
}

function filterUserId(userNo, siteNo) {
    for (var i = 0; i < stats[userNo].ids.length; i++)
        if (stats[userNo].ids[i].substr(0, stats[userNo].ids[i].indexOf("_") + 1) == sites[siteNo].prefix)
            return stats[userNo].ids[i].substr(stats[userNo].ids[i].indexOf("_") + 1);
    return "";
}

function filterProblems(userNo, siteNo) {
    var filteredProblems = [];
    for (var i = 0; i < stats[userNo].problems.length; i++)
        if (stats[userNo].problems[i].substr(0, stats[userNo].problems[i].indexOf("_") + 1) == sites[siteNo].prefix)
            filteredProblems.push(stats[userNo].problems[i].substr(stats[userNo].problems[i].indexOf("_") + 1));
    filteredProblems.sort(strnatcmp);
    return filteredProblems;
}

function shortenProblemId(problemId) {
    return problemId.length <= 8 ? problemId : problemId.substr(0, 8) + "...";
}

function getProblemList(userNo, siteNo) {
    var T_COL = 15;
    var userId = filterUserId(userNo, siteNo);
    var problems = filterProblems(userNo, siteNo);
    if (!problems.length)
        return "";
    var h = "<h3>Задачи <a href=\"" + sites[siteNo].url + "\">" + sites[siteNo].name + "</a> " +
            "(решено: " + (problems.length ? "<a href=\"" + sites[siteNo].userUrl(userId) + "\">" + problems.length + "</a>" : 0) + ")</h3>";
    h += "<table class=\"problems\">";
    for (var i = 0; i < problems.length || i % T_COL; i++) {
        if (i % T_COL == 0)
            h += "<tr>";
        h += "<td>"
        if (i < problems.length)
            h += "<a href=\"" + sites[siteNo].problemUrl(problems[i]) + "\">" + shortenProblemId(problems[i]) + "</a>";
        h += "</td>";
        if (i % T_COL == T_COL - 1)
            h += "</tr>";
    }
    h += "</table>";
    return h;
}

function getCmpProblemList(userNoA, userNoB, siteNo) {
    var T_COL = 15;
    var userIdA = filterUserId(userNoA, siteNo), userIdB = filterUserId(userNoB, siteNo);;
    var problemsA = filterProblems(userNoA, siteNo), problemsB = filterProblems(userNoB, siteNo);
    if (!problemsA.length && !problemsB.length)
        return "";
    var h = "<h3>Задачи <a href=\"" + sites[siteNo].url + "\">" + sites[siteNo].name + "</a> (решено: " +
            "<span class=\"txtMarkA\">" + (problemsA.length ? "<a href=\"" + sites[siteNo].userUrl(userIdA) + "\">" + problemsA.length + "</a>" : 0) + "</span> / " +
            "<span class=\"txtMarkB\">" + (problemsB.length ? "<a href=\"" + sites[siteNo].userUrl(userIdB) + "\">" + problemsB.length + "</a>" : 0) + "</span>)</h3>";
    h += "<table class=\"problems\">";
    for (var i = 0, j = 0, k = 0; i < problemsA.length || j < problemsB.length || k % T_COL; k++) {
        if (k % T_COL == 0)
            h += "<tr>";
        if (i < problemsA.length && (j >= problemsB.length || strnatcmp(problemsA[i], problemsB[j]) < 0)) {
            h += "<td class=\"tdMarkA\"><a href=\"" + sites[siteNo].problemUrl(problemsA[i]) + "\">" + shortenProblemId(problemsA[i]) + "</a></td>";
            i++;
        } else if (j < problemsB.length && (i >= problemsA.length || strnatcmp(problemsA[i], problemsB[j]) > 0)) {
            h += "<td class=\"tdMarkB\"><a href=\"" + sites[siteNo].problemUrl(problemsB[j]) + "\">" + shortenProblemId(problemsB[j]) + "</a></td>";
            j++;
        } else if (i < problemsA.length && j < problemsB.length) {
            h += "<td><a href=\"" + sites[siteNo].problemUrl(problemsA[i]) + "\">" + shortenProblemId(problemsA[i]) + "</a></td>";
            i++;
            j++;
        } else {
            h += "<td></td>";
        }
        if (k % T_COL == T_COL - 1)
            h += "</tr>";
    }
    h += "</table>";
    return h;
}

function printStats(userNo) {
    var h = "<h2>Решения пользователя " + stats[userNo].userName + " (всего: " + stats[userNo].problems.length + ")</h2>";
    h += "<h2>(<a href=\"javascript:printRating();\">назад</a>)</h2>";
    for (var siteNo = 0; siteNo < sites.length; siteNo++)
        h += getProblemList(userNo, siteNo);
    document.getElementById("container").innerHTML = h;
}

function printCmpStats(userNoA, userNoB) {
    var h = "<h2>Сравнение решений пользователей<br>" +
            "<span class=\"txtMarkA\">A</span> &mdash; " + stats[userNoA].userName + " (всего: " + stats[userNoA].problems.length + ")<br>" +
            "<span class=\"txtMarkB\">B</span> &mdash; " + stats[userNoB].userName + " (всего: " + stats[userNoB].problems.length + ")</h2>";
    h += "<h2>(<a href=\"javascript:printRating();\">назад</a>)</h2>";
    for (var siteNo = 0; siteNo < sites.length; siteNo++)
        h += getCmpProblemList(userNoA, userNoB, siteNo);
    document.getElementById("container").innerHTML = h;
}

var cmpNoA = -1, cmpNoB = -1;

function sortUsers(usersSortMode) {
    if (users.length > 1) {
        users.sort(
            function(a, b) {
                if (usersSortMode == -2)
                    return strcmp(a.name, b.name);
                if (usersSortMode == -1)
                    return b.total - a.total ? b.total - a.total : strcmp(a.name, b.name);
                return b.problemsCnt[usersSortMode] - a.problemsCnt[usersSortMode] ? b.problemsCnt[usersSortMode] - a.problemsCnt[usersSortMode] : strcmp(a.name, b.name);
            }
        );
    }
    if (cmpNoA == -1)
        cmpNoA = users[0].userNo;
    if (cmpNoB == -1)
        cmpNoB = users[Math.min(users.length - 1, 1)].userNo;
    printRating();
}

function printRating() {
    var h = "<table class=\"rating\">";
    h += "<tr><td>#</td>";
    h += "<td><span class=\"sortButton\" onclick=\"javascript:sortUsers(-2);\">Участник</span></td>";
    for (var i = 0; i < sites.length; i++)
        h += "<td class=\"tdCount\"><span class=\"sortButton\" onclick=\"javascript:sortUsers(" + i + ");\">" + sites[i].name + "</span></td>";
    h += "<td class=\"tdCount\"><span class=\"sortButton\" onclick=\"javascript:sortUsers(-1);\">Всего</span></td>";
    h += "<td class=\"tdCompare\" colspan=2><a href=\"javascript:printCmpStats(cmpNoA, cmpNoB);\">Сравнить выбранных</a></td></tr>";
    for (var i = 0; i < users.length; i++) {
        h += "<tr><td>" + (i + 1) + "</td><td class=\"userBlock\"><a href=\"javascript:printStats('" + users[i].userNo + "');\">" + users[i].name + "</a></td>";
        for (var j = 0; j < sites.length; j++)
            h += "<td>" + users[i].problemsCnt[j] + "</td>";
        h += "<td>" + users[i].total + "</td>";
        h += "<td><input name=\"radioA\" type=\"radio\"" + (users[i].userNo == cmpNoA ? " checked" : "") + " onclick=\"cmpNoA =" + users[i].userNo + ";\"></td>";
        h += "<td><input name=\"radioB\" type=\"radio\"" + (users[i].userNo == cmpNoB ? " checked" : "") + " onclick=\"cmpNoB =" + users[i].userNo + ";\"></td>";
        h += "</tr>";
    }
    h += "</table>";
    h += "<span id=\"trainingsContainer\"></span>";
    document.getElementById("container").innerHTML = h;
    printTrainings();
}

//=== TRAININGS ====================================================================

function getUserNo(userName) {
    for (var i = 0; i < stats.length; i++)
        if (stats[i].userName == userName)
            return i;
    return -1;
}

function getProblemUrl(prefixedProblemId) {
    var prefix = prefixedProblemId.substr(0, prefixedProblemId.indexOf("_") + 1);
    var problemId = prefixedProblemId.substr(prefixedProblemId.indexOf("_") + 1);
    for (var i = 0; i < sites.length; i++)
        if (sites[i].prefix == prefix)
            return sites[i].problemUrl(problemId);
    return "";
}

function printTrainings() {
    var h = "";
    for (var ti = 0; ti < trainings.length; ti++) {
        h += "<div class=\"trainingHeader\">" + trainings[ti].name + "</div><table class=\"trainings\"><tr><td rowspan=2></td>";
        for (var pi = 0; pi < trainings[ti].parts.length; pi++)
            h += "<td colspan=" + trainings[ti].parts[pi].problems.length + ">" + trainings[ti].parts[pi].name + "</td>";
        h += "</tr><tr>";
        for (var pi = 0; pi < trainings[ti].parts.length; pi++)
            for (var pj = 0; pj < trainings[ti].parts[pi].problems.length; pj++)
                h += "<td class=\"problemBlock\"><a href=\"" + getProblemUrl(trainings[ti].parts[pi].problems[pj].id) + "\">" + trainings[ti].parts[pi].problems[pj].code + "</a></td>";
        h += "</tr>";
        var userResults = [];
        for (var ui = 0; ui < trainings[ti].users.length; ui++) {
            var userResult = { userName: trainings[ti].users[ui], userNo: getUserNo(trainings[ti].users[ui]), totalProblems: 0, problemResults: [] };
            for (var pi = 0; pi < trainings[ti].parts.length; pi++) {
                for (var pj = 0; pj < trainings[ti].parts[pi].problems.length; pj++) {
                    var problemSolved = (stats[userResult.userNo].problems.indexOf(trainings[ti].parts[pi].problems[pj].id) != -1);
                    userResult.problemResults.push(problemSolved);
                    userResult.totalProblems += problemSolved;
                }
            }
            userResults.push(userResult);
        }
        userResults.sort(function(a, b) { return b.totalProblems - a.totalProblems; });
        for (var ui = 0; ui < userResults.length; ui++) {
            h += "<tr><td class=\"userBlock\"><a href=\"javascript:printStats('" + userResults[ui].userNo + "');\">" + userResults[ui].userName + "</a></td>";
            for (var pi = 0; pi < userResults[ui].problemResults.length; pi++)
                h += "<td class=\"problemBlock\">" + (userResults[ui].problemResults[pi] ? "+" : "") + "</td>";
            h += "</tr>";
        }
        h += "</table>";
    }
    document.getElementById("trainingsContainer").innerHTML = h;
}

//==================================================================================

document.body.onload = function() { sortUsers(-1); };

