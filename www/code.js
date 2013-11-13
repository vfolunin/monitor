
function getProblemList(siteName, siteUrl, stats) {
    var T_COL = 15;
    if (!stats.problems.length)
        return "";
    var h = "<h3>Задачи <a href=\"" + siteUrl + "\">" + siteName + "</a> " +
            "(решено: " + (stats.problems.length ? "<a href=\"" + stats.userPage + "\">" + stats.problems.length + "</a>" : 0) + ")</h3>";
    h += "<table class=\"problems\">";
    for (var i = 0; i < stats.problems.length || i % T_COL; i++) {
        if (i % T_COL == 0)
            h += "<tr>";
        h += "<td>"
        if (i < stats.problems.length)
            h += "<a href=\"" + stats.problems[i].url + "\">" + stats.problems[i].id + "</a>";
        h += "</td>";
        if (i % T_COL == T_COL - 1)
            h += "</tr>";
    }
    h += "</table>";
    return h;
}

function getCmpProblemList(siteName, siteUrl, statsA, statsB) {
    var T_COL = 15;
    if (!statsA.problems.length && !statsB.problems.length)
        return "";
    var h = "<h3>Задачи <a href=\"" + siteUrl + "\">" + siteName + "</a> (решено: " +
            "<span class=\"txtMarkA\">" + (statsA.problems.length ? "<a href=\"" + statsA.userPage + "\">" + statsA.problems.length + "</a>" : 0) + "</span> / " +
            "<span class=\"txtMarkB\">" + (statsB.problems.length ? "<a href=\"" + statsB.userPage + "\">" + statsB.problems.length + "</a>" : 0) + "</span>)</h3>";
    h += "<table class=\"problems\">";
    for (var i = 0, j = 0, k = 0; i < statsA.problems.length || j < statsB.problems.length || k % T_COL; k++) {
        if (k % T_COL == 0)
            h += "<tr>";
        if (i < statsA.problems.length && (j >= statsB.problems.length || strnatcmp(statsA.problems[i].id, statsB.problems[j].id) < 0)) {
            h += "<td class=\"tdMarkA\"><a href=\"" + statsA.problems[i].url + "\">" + statsA.problems[i].id + "</a></td>";
            i++;
        } else if (j < statsB.problems.length && (i >= statsA.problems.length || strnatcmp(statsA.problems[i].id, statsB.problems[j].id) > 0)) {
            h += "<td class=\"tdMarkB\"><a href=\"" + statsB.problems[j].url + "\">" + statsB.problems[j].id + "</a></td>";
            j++;
        } else if (i < statsA.problems.length && j < statsB.problems.length) {
            h += "<td><a href=\"" + statsA.problems[i].url + "\">" + statsA.problems[i].id + "</a></td>";
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
        h += getProblemList(sites[i].name, sites[i].url, stats[id].stats[i]);
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
        h += getCmpProblemList(sites[i].name, sites[i].url, stats[idA].stats[i], stats[idB].stats[i]);
    document.getElementById("container").innerHTML = h;
}

var sites = [
    { name : "ACMP", url : "http://acmp.ru/" },
    { name : "Timus Online Judge", url : "http://acm.timus.ru/" },
    { name : "СГУ", url : "http://acm.sgu.ru/" },
    { name : "МЦНМО", url : "http://informatics.mccme.ru/" },
    { name : "Codeforces", url : "http://codeforces.ru/" }
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

