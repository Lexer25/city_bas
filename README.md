# city_bas

2.0.5
10.10.2026
[*]
Модальное окно переведено на Bootstrap Modal (bootstrap 3.3.1 из общего шаблона) вместо самодельного окна на CSS-чек-боксе:
разметка .modal/.modal-dialog/.modal-content/.modal-header/.modal-body/.modal-footer, открытие через data-toggle="modal" data-target="#basModal",
закрытие через data-dismiss="modal" и кнопкой "Закрыть", работают Esc и клик по фону. Классы .modalm в модуле больше не используются.
Содержимое окна оформлено средствами Bootstrap: три вкладки (nav-tabs) - "IP адрес", "Логин и пароль", "Дата последнего события",
поля form-control, кнопки btn-primary, окно modal-lg. Новые строки i18n не требуются.
Высота тела окна ограничена (прокрутка) - в окне несколько форм.
Исправлено: обработчик кнопки в таблице привязан к .popup-contact, а не ко всем .btn - иначе нажатие кнопок внутри окна сбрасывало заполненные поля.
Исправлено: конфликт с проектным сборным bootstrap.min.css, из-за которого окно открывалось "под серой вуалью" (окно видно сквозь затемнение,
а клик в любом месте закрывал его). Причина: в bootstrap.min.css правило .modal-dialog задано с z-index:1050, но БЕЗ position, поэтому z-index
не действовал, а .modal-backdrop (z-index:1030), который bootstrap.min.js вставляет внутрь .modal, рисовался поверх диалога. В представлении
диалог поднят над вуалью (#basModal .modal-dialog { position: relative; z-index: 1060; left: auto; right: auto; }), добавлен data-backdrop="static" -
клик по затемнению окно не закрывает, закрытие кнопками "Закрыть"/"x"/Esc. Класс modal-lg убран: в проектном CSS он не определён.
Причина оказалась общей для проекта: bootstrap.min.css - версия 3.0.0, а bootstrap.min.js - 3.3.1. В 3.0.0 вуаль добавлялась в body
(.appendTo(document.body)) и её z-index:1030 был ниже .modal (1040), а в 3.3.1 вуаль вставляется внутрь .modal и в CSS 3.3.1 у неё нет z-index.
Поэтому то же правило добавлено глобально в static/css/city.css (.modal-dialog { position: relative; z-index: 1060; left: auto; right: auto; }) -
оно чинит модальные окна всех модулей (floorplan, mancard и др.).
Исправлено: для признака "весь журнал событий" (strvalue=1) календарь больше не заполняется датой 01.01.1970.

2.0.4
10.10.2026
[+]
В модальное окно добавлена дата последнего события (параметр LASTEVENT таблицы bas_param).
Дата выводится календарём (поле datetime-local), можно выбрать любую дату до текущего времени и сохранить её кнопкой "Сохранить".
Выбранная дата сохраняется в формате linux (миллисекунды) в колонку STRVALUE.
Добавлен чек-бокс "Выбрать весь журнал событий": при установке в колонку STRVALUE записывается 1.
Модель Model_Bas: методы getLastEvent (SELECT * FROM bas_param WHERE id_dev=<id_dev> AND param='LASTEVENT'),
setLastEventTimestamp (UPDATE bas_param SET strvalue=<метка времени в миллисекундах>), setLastEventAllLog (strvalue=1).
Контроллер: действие bas/update (todo=bas_setLastEvent) и bas/getLastEvent (JSON для модального окна).
id_dev берётся у панели, для которой открыто модальное окно (по умолчанию 555 - Model_Bas::ID_DEV_LASTEVENT_DEFAULT).

2.0.3
3.10.2026
[*]
Добавил строку с нумерацией колонок.
Добавил название точек прохода и ссылку на свойства точки прохода.


2.0.2 28.09.2026 Удаляю ненужные файлы.