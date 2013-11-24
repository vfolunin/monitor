
var sites = [
    {
        name : "ACMP",
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
        url : "http://codeforces.ru/",
        userUrl : function(uid) {
            return this.url + "profile/" + uid;
        },
        problemUrl : function(pid) {
            var i = 0;
            while (i < pid.length && pid.charAt(i) >= 0 && pid.charAt(i) <= 9)
                i++;
            return this.url + "problemset/" + (pid.substr(0, 3) == "100" ? "gymProblem/" : "problem/") + pid.slice(0, i) + "/" + pid.slice(i);
        }
    }
];

var users = [];
for (var i = 0; i < stats.length; i++) {
    var user = {};
    user.id = i;
    user.name = stats[i].userName;
    user.total = 0;
    user.problems = [];
    for (var j = 0; j < sites.length; j++) {
        user.problems.push(stats[i].stats[j].problems.length);
        user.total += stats[i].stats[j].problems.length;
    }
    users.push(user);
}

function getProblemList(userId, siteId) {
    var T_COL = 15;
    var st = stats[userId].stats[siteId];
    if (!st.problems.length)
        return "";
    var h = "<h3>Задачи <a href=\"" + sites[siteId].url + "\">" + sites[siteId].name + "</a> " +
            "(решено: " + (st.problems.length ? "<a href=\"" + sites[siteId].userUrl(st.userId) + "\">" + st.problems.length + "</a>" : 0) + ")</h3>";
    h += "<table class=\"problems\">";
    for (var i = 0; i < st.problems.length || i % T_COL; i++) {
        if (i % T_COL == 0)
            h += "<tr>";
        h += "<td>"
        if (i < st.problems.length)
            h += "<a href=\"" + sites[siteId].problemUrl(st.problems[i]) + "\">" + st.problems[i] + "</a>";
        h += "</td>";
        if (i % T_COL == T_COL - 1)
            h += "</tr>";
    }
    h += "</table>";
    return h;
}

function getCmpProblemList(userIdA, userIdB, siteId) {
    var T_COL = 15;
    var stA = stats[userIdA].stats[siteId], stB = stats[userIdB].stats[siteId];
    if (!stA.problems.length && !stB.problems.length)
        return "";
    var h = "<h3>Задачи <a href=\"" + sites[siteId].url + "\">" + sites[siteId].name + "</a> (решено: " +
            "<span class=\"txtMarkA\">" + (stA.problems.length ? "<a href=\"" + sites[siteId].userUrl(stA.userId) + "\">" + stA.problems.length + "</a>" : 0) + "</span> / " +
            "<span class=\"txtMarkB\">" + (stB.problems.length ? "<a href=\"" + sites[siteId].userUrl(stB.userId) + "\">" + stB.problems.length + "</a>" : 0) + "</span>)</h3>";
    h += "<table class=\"problems\">";
    for (var i = 0, j = 0, k = 0; i < stA.problems.length || j < stB.problems.length || k % T_COL; k++) {
        if (k % T_COL == 0)
            h += "<tr>";
        if (i < stA.problems.length && (j >= stB.problems.length || strnatcmp(stA.problems[i], stB.problems[j]) < 0)) {
            h += "<td class=\"tdMarkA\"><a href=\"" + sites[siteId].problemUrl(stA.problems[i]) + "\">" + stA.problems[i] + "</a></td>";
            i++;
        } else if (j < stB.problems.length && (i >= stA.problems.length || strnatcmp(stA.problems[i], stB.problems[j]) > 0)) {
            h += "<td class=\"tdMarkB\"><a href=\"" + sites[siteId].problemUrl(stB.problems[j]) + "\">" + stB.problems[j] + "</a></td>";
            j++;
        } else if (i < stA.problems.length && j < stB.problems.length) {
            h += "<td><a href=\"" + sites[siteId].problemUrl(stA.problems[i]) + "\">" + stA.problems[i] + "</a></td>";
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

function printStats(id) {
    var total = 0;
    for (var i = 0; i < sites.length; i++)
        total += stats[id].stats[i].problems.length;
    var h = "<h2>Решения пользователя " + stats[id].userName + " (всего: " + total + ")</h2>";
    h += "<h2>(<a href=\"javascript:printRating();\">назад</a>)</h2>";
    for (var i = 0; i < sites.length; i++)
        h += getProblemList(id, i);
    document.getElementById("container").innerHTML = h;
}

function printCmpStats(idA, idB) {
    var totalA = 0, totalB = 0;
    for (var i = 0; i < sites.length; i++) {
        totalA += stats[idA].stats[i].problems.length;
        totalB += stats[idB].stats[i].problems.length;
    }
    var h = "<h2>Сравнение решений пользователей<br>" +
            "<span class=\"txtMarkA\">A</span> &mdash; " + stats[idA].userName + " (всего: " + totalA + ")<br>" +
            "<span class=\"txtMarkB\">B</span> &mdash; " + stats[idB].userName + " (всего: " + totalB + ")</h2>";
    h += "<h2>(<a href=\"javascript:printRating();\">назад</a>)</h2>";
    for (var i = 0; i < sites.length; i++)
        h += getCmpProblemList(idA, idB, i);
    document.getElementById("container").innerHTML = h;
}

var cmpIdA = -1, cmpIdB = -1;

function sortUsers(usersSortMode) {
    if (users.length > 1) {
        users.sort(
            function(a, b) {
                if (usersSortMode == -2)
                    return strcmp(a.name, b.name);
                if (usersSortMode == -1)
                    return b.total - a.total ? b.total - a.total : strcmp(a.name, b.name);
                return b.problems[usersSortMode] - a.problems[usersSortMode] ? b.problems[usersSortMode] - a.problems[usersSortMode] : strcmp(a.name, b.name);
            }
        );
    }
    if (cmpIdA == -1)
        cmpIdA = users[0].id;
    if (cmpIdB == -1)
        cmpIdB = users[Math.min(users.length - 1, 1)].id;
    printRating();
}

function printRating() {
    var h = "<table class=\"rating\">";
    h += "<tr><td>#</td>";
    h += "<td><span class=\"sortButton\" onclick=\"javascript:sortUsers(-2);\">Участник</span></td>";
    for (var i = 0; i < sites.length; i++)
        h += "<td class=\"tdCount\"><span class=\"sortButton\" onclick=\"javascript:sortUsers(" + i + ");\">" + sites[i].name + "</span></td>";
    h += "<td class=\"tdCount\"><span class=\"sortButton\" onclick=\"javascript:sortUsers(-1);\">Всего</span></td>";
    h += "<td class=\"tdCompare\" colspan=2><a href=\"javascript:printCmpStats(cmpIdA, cmpIdB);\">Сравнить выбранных</a></td></tr>";
    for (var i = 0; i < users.length; i++) {
        h += "<tr><td>" + (i + 1) + "</td><td><a href=\"javascript:printStats('" + users[i].id + "');\">" + users[i].name + "</a></td>";
        for (var j = 0; j < sites.length; j++)
            h += "<td>" + users[i].problems[j] + "</td>";
        h += "<td>" + users[i].total + "</td>";
        h += "<td><input name=\"radioA\" type=\"radio\"" + (users[i].id == cmpIdA ? " checked" : "") + " onclick=\"cmpIdA =" + users[i].id + ";\"></td>";
        h += "<td><input name=\"radioB\" type=\"radio\"" + (users[i].id == cmpIdB ? " checked" : "") + " onclick=\"cmpIdB =" + users[i].id + ";\"></td>";
        h += "</tr>";
    }
    h += "</table>";
    document.getElementById("container").innerHTML = h;
}

document.body.onload = function() { sortUsers(-1); };

