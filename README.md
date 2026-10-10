# city_bas

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