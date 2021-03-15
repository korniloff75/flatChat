# flatChat
Чат с использованием **HTML + JS + PHP**. Рабочий пример -- http://flatchat.js-master.ru

Чат хранит свои данные в файлах, без использования баз данных, что сокращает процедуру его установки до простого копирования.

Движок чата имеет расширенные возможности, позволяющие пользователям загружать в посты изображения, озвучивать существующие посты. Для ускорения работы движок ограничивает количество загружаемых в чат постов. При этом остальные посты остаются доступными в архивах чата.

Имеется возможность кастомизации шаблона.

Чат связывается с сервером через ajax long polling.

-----

При заходе под Администратором появляются возможности редактирования / удаления постов.

Для смены админ-пароля удалите файл **/assets/adm.json**. После этого, при первой попытке залогиниться введите любой пароль, который станет административным.

Прототип до начала разработки -- https://protocoder.ru/minichat

<a rel="license" href="http://creativecommons.org/licenses/by-sa/4.0/"><img alt="Лицензия Creative Commons" style="border-width:0" src="https://i.creativecommons.org/l/by-sa/4.0/88x31.png" /></a><br />Это произведение доступно по <a rel="license" target="_blank" href="http://creativecommons.org/licenses/by-sa/4.0/">лицензии Creative Commons «Attribution-ShareAlike» («Атрибуция-СохранениеУсловий») 4.0 Всемирная</a>.
