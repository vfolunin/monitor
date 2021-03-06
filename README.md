## Монитор решений задач по программированию ##

Монитор предназначен для автоматического сбора и объединения статистики решений задач по программированию, размещённых в онлайн-архивах с системой автоматической проверки. В настоящий момент монитор поддерживает следующие архивы:
* [ACMP][acmp]
* [МЦНМО][mccme]
* [Timus Online Judge][timus]
* [Codeforces][cf]
* [E-olymp][eolymp]
* [Sphere Online Judge][spoj]
* [UVa Online Judge][uva]
* [CSES Problemset][cses]

Монитор специально создавался для работы с популярными российскими архивами, не представленными в аналогичных зарубежных системах.

Монитор позволяет:
* Вести таблицу пользователей, отражающую текущее количество решённых ими задач в каждом из поддерживаемых архивов, а также общее количество решённых задач; упорядочивать пользователей по количеству решённых задач в каждом из архивов или по общему количеству решённых задач;
* Отображать для указанного пользователя количество решённых им задач, ссылки на страницы каждой решённой задачи и ссылки на профили пользователя в каждом из архивов;
* Для двух выбранных пользователей строить таблицу сравнения решений, в которой отображаются количество решённых пользователями задач, ссылки на задачи, решённые каждым из пользователей по отдельности и обоими пользователями, и ссылки на профили пользователей в каждом из архивов;
* Составлять сводные таблицы решения конкретных задач конкретными пользователями (для организации тренировок, контрольных работ и тому подобного).

### Состав ###

Файлы, необходимые для отображения монитора:

* index.html — HTML-страница для отображения монитора;
* style.css — лист стилей для отображения монитора;
* code.js — содержит функции отображения монитора;
* strnatcmp.js — содержит функцию [``strnatcmp``][strnatcmp];
* trainings.js — содержит описания таблиц задач (тренировок).

Файлы, необходимые для обновления монитора:

* monitor.php — содержит функции обновления статистики решений;
* users.php — содержит описания пользователей монитора.

### Использование монитора ###

#### Добавление пользователей ####

Для добавления пользователей требуется отредактировать содержимое файла users.php. В данном файле определяется массив ``$users``, каждый элемент которого содержит описание одного пользователя.

```php
$users = array();

//$users[] = array(                 "Имя участника", "ACMP ID", "МЦНМО ID", "Timus ID", "Codeforces ID", "E-olymp ID", "SPOJ ID", "UVa ID", "CSES ID");
$users[]   = array("Фолунин Владимир Александрович",    "4876",    "38459",    "96779",       "ctrlalt",    "ctrlalt", "ctrlalt", "882414",   "27145");
```

Описание является строковым массивом, начальный элемент которого содержит имя, под которым пользователь будет отображаться в мониторе; остальные элементы содержат идентификаторы пользователя в архивах. Порядок идентификаторов должен соответствовать порядку регистрации архивов в файлах code.js и monitor.php. Если пользователь не зарегистрирован в некотором архиве, соответствующий элемент массива должен быть пустой строкой.

#### Особенности архива МЦНМО ####

Для получения списка решений в архиве [МЦНМО][mccme] требуется авторизация.

Чтобы монитор мог авторизоваться, впишите актуальные логин и пароль пользователя в константы ``$MCCME_USERNAME`` и ``$MCCME_PASSWORD`` в файле monitor.php.

#### Обновление монитора ####

Для обновления монитора следует запустить сценарий monitor.php. Этот сценарий осуществляет запрос и парсинг страниц со статистикой решений всех пользователей на каждом из архивов, в итоге порождая файл stats.js, содержащий совокупную статистику решений, использующуюся при отображении монитора. При каждом запуске сценария файл stats.js пересоздаётся заново.

Чтобы автоматизировать обновление монитора, добавьте запуск сценария monitor.php в планировщик задач.

#### Добавление таблиц задач (тренировок) ####

Монитор позволяет отображать на главной странице таблицы решений выбранными пользователями конкретных задач различных архивов. Эти таблицы могут быть полезны при организации тренировок, контрольных работ, домашних заданий и тому подобного.

Описания таблиц содержатся в массиве ``trainings[]``, определённом в файле trainings.js.

```javascript
trainings = [
    {
        name: "Стартовые задачи",
        users: [
            "Фолунин Владимир Александрович"
        ],
        parts: [
            {
                name: "",
                problems: [
                    { code: "A", id: "acmp_1" },
                    { code: "B", id: "mccme_1" },
                    { code: "C", id: "timus_1000" }
                ]
            },
            {
                name: "Codeforces",
                problems: [
                    { code: "D", id: "cf_1.A" },
                    { code: "E", id: "cf_100001.A" },
                    { code: "F", id: "cf_sgu.100" }
                ]
            },
            {
                name: "",
                problems: [
                    { code: "G", id: "eolymp_1" },
                    { code: "H", id: "spoj_TEST" },
                    { code: "I", id: "uva_100" },
                    { code: "J", id: "cses_1068" }
                ]
            }
        ]
    }
];
```

Каждое описание представляет собой объект, содержащий следующие поля:
* ``name`` — название таблицы, отображаемое в её заголовке;
* ``users`` — строковый массив, содержащий имена всех пользователей, решения которых должны отображаться в таблице;
* ``parts`` — массив, содержащий описания частей таблицы (наборов задач), каждое из которых является объектом со следующими полями:
    * ``name`` — название части, отображаемое в таблице;
    * ``problems`` — массив, содержащий описания задач, каждое из которых является объектом со следующими полями:
        * ``code`` — префикс (код, название) задачи, отображаемое в таблице;
        * ``id`` — идентификатор задачи.

### Добавление нового архива ###

Для добавления архива требуется проделать следующие действия:

* Файл monitor.php:
    * Определить метод, принимающий имя учётной записи пользователя в добавляемом архиве (без префикса) и возвращающий неупорядоченный массив идентификаторов задач (без префиксов), решённых указанным пользователем в рассматриваемом архиве;
    * Добавить созданный метод в массив ``$getFunctions``;
    * Придумать префикс, идентифицирующий добавляемый архив, и добавить этот префикс в массив ``$prefixes``.
* Файл users.php:
    * Указать в описаниях пользователей их идентификаторы в добавляемом архиве.
* Файл code.js:
    * Создать описывающий добавляемый архив объект, в полях ``name``, ``prefix`` и ``url`` которого указать имя архива, префикс и URL главной страницы;
    * Добавить в объект методы ``userUrl()`` и ``problemUrl()``, возвращающие URL страницы пользователя по имени его учётной записи (без префикса) и URL задачи по её коду (без префикса);
    * Добавить созданный объект в массив ``sites``.


[acmp]:https://acmp.ru
[mccme]:https://informatics.msk.ru
[timus]:https://acm.timus.ru
[cf]:https://codeforces.com
[eolymp]:https://www.e-olymp.com
[spoj]:https://www.spoj.com
[uva]:https://onlinejudge.org
[cses]:https://cses.fi/problemset
[strnatcmp]:https://github.com/kvz/phpjs/blob/master/functions/strings/strnatcmp.js