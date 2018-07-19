# rbkmoney-cms-opencart


### На русском

### Установка и настройка модуля

#### Установка

1. Первый способ

Заархивируйте папку `upload` в `zip` архив и переименуйте его в `rbkmoney-payment.ocmod.zip`
После чего необходимо зайти в `Extension Installer`, нажать `Upload` и выбрать архив для установки: `rbkmoney-payment.ocmod.zip`

2. Второй способ

Для установки модуля скопируйте содержимое каталога `upload`:

```
<OpenCart>/admin/
<OpenCart>/catalog/
```

#### Настройка модуля

Затем в панели администратора установите и настройте его:

```
Extensions > Payments > RBKmoney нажать [Install]
Extensions > Payments > RBKmoney нажать [Edit] и заполнить необходимые настройки
```

Для начала приема платежей на Вашем сайте необходимо:

- Зарегистрироваться на https://dashboard.rbk.money
- Получить необходимые данные для настройки модуля
- Настроить модуль

#### Совместимость

- Opencart 2.1.0.2

В списке совместимости нет вашей версии Opencart 2.x? Напишите нам обращение - это ускорит процесс адаптации модуля под вашу систему.


#### Проблемы и решения

1. Ошибка: FTP должен быть включен в настройках

Два варианта решения:

- Если при установке модуля из админки отображается эта ошибка - вам нужно установить бесплатную FTP QuickFix модификацию localcopy.ocmod.xml. Она установиться без проблем через тот же установщик дополнений, только после установки не забудьте обновить модификации и затем можете приступать к установке любых модулей на Opencart 2.

- Также решить эту ошибку можно по другому: прописать доступы к FTP в админке Система > Настройки > Магазин > вкладка FTP.

2. Ошибка: Доступ запрещен!

Если вы видите сообщение "Доступ запрещен!  У Вас нет прав для доступа к этой странице. Если она Вам нужна, обратитесь к администратору." -  нужно дать права администраторам на управление модулем или страницей.

Решение: в админке Opencart 2 переходим в Система > Пользователи > Группы пользователей > Администраторы и здесь нажимаем "Выделить все" ниже обоих блоков, затем Сохранить.

3. Ошибка: Недопустимый тип файла!

Если модуль - это один XML файл,  то его расширение должно быть .ocmod.xml

Если модуль - это ocmod.zip архив, то его не нужно распаковывать, а устанавливать как есть. В таком архиве обязательно должна быть папка upload (может быть пустой), а также могут быть файлы модификаций: install.xml, install.php, install.sql. Никаких других файлов в корне архива быть не должно.

Читайте подробнее как [устанавливать модули в Opencart 2](https://opencart2x.ru/blog/install-module)


4. Ошибка: Каталог, содержащий файлы для загрузки не может быть найден!

Эта ошибка означает, что в загружаемом архиве отсутсвует папка upload. Даже если у модуля нет файлов, кроме модификаций - эта папка должна присутствовать в архиве модуля .ocmod.zip, тогда она должна оставаться пустой.

5. Ошибка: Модификатор использует тот же ID код который вы пытаетесь загрузить!

Эта ошибка означает, что вы пытаетесь установить модификатор, который уже установлен или, возможно, у какого-то вашего модуля такой же ID.

Для решения этой ошибки вам нужно перед установкой удалить старую версию модификации в разделе Модули > Модификации.

Если такого модуля у вас нет, но совпадает ID, тогда нужно поменять значение параметра `<code>` в устанавливаемом модификаторе XML, сделать этот параметр уникальным дописав несколько символов.

6. Ошибка: `Warning: DOMDocument::loadXML(): CData section not finished`

Эта ошибка означает, что вы пытаетесь установить слишком объемный xml-модификатор.

Количество символов в `ocmod.xml` файле не должно превышать 65535.

Для решения ошибки нужно разбить xml-файл модификации на несколько частей, главное - не забыть задавать каждой уникальное значение в `<code>`, можно добавлять к текущему значению цифры 1,2,3... как идентификаторы части.

Еще одним способом решения есть изменения типа в поля, где храняться модификации, в таблице `'oc_modification'` базы данных. Нужно выполнить следующий SQL-запрос:

```
ALTER TABLE oc_modification CHANGE xml xml MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
```

---

### In English

### Installing and Configuring Module

#### Installation

1. First way

Archive `upload` folder to ` zip` archive and rename it to `rbkmoney-payment.ocmod.zip`
After that you navigate to `Extension Installer`, click` Upload` and select archive to install: `rbkmoney-payment.ocmod.zip`

2. Second way

To install module, copy contents of `upload` directory:

`` `
<OpenCart> / admin /
<OpenCart> / catalog /
`` `

#### Module configuration

Then in admin panel, install and configure it:

`` `
Extensions> Payments> RBKmoney click [Install]
Extensions> Payments> RBKmoney click [Edit] and fill in necessary settings
`` `

To start accepting payments on your site you need:

- Register on https://dashboard.rbk.money
- Get necessary data to configure module
- Module configuration

#### Compatibility

- Opencart 2.1.0.2

Is your version of Opencart 2.x not listed in compatibility list? Write us an appeal - this will speed up the process of adapting the module to your system.


#### Problems and solutions

1. Error: FTP must be enabled in settings

Two solutions:

- If installing this module from admin panel displays this error - you need to install free FTP QuickFix modification of localcopy.ocmod.xml. It can be installed without problems through same add-on installer, only after installation do not forget to update modifications and then you can proceed to install any modules on Opencart 2.

- You can also solve this error differently: register FTP accesses in System> Settings> Store> FTP tab.

2. Error: Access is denied!

If you see message "Access Denied! You are not authorized to access this page." If you need it, contact administrator. " - You need to give administrators rights to manage module or page.

Solution: In Opencart 2 admin panel, go to System> Users> User Groups> Administrators and click "Select All" below both blocks, then Save.

3. Error: Invalid file type!

If module is one XML file, then its extension must be .ocmod.xml

If module is an ocmod.zip archive, then it does not need to be unpacked, but installed as it is. In such an archive must necessarily be folder upload (may be empty), and also there may be modification files: install.xml, install.php, install.sql. There should not be any other files in root of archive.

Read more about how to [install modules in Opencart 2] (https://opencart2x.ru/blog/install-module)


4. Error: directory containing files for download can not be found!

This error indicates that uploaded archive does not have upload folder. Even if module does not have files other than modifications - this folder should be present in archive of module .ocmod.zip, then it should remain empty.

5. Error: modifier uses same ID code that you are trying to load!

This error means that you are trying to install a modifier that is already installed, or perhaps some of your module has same ID.

To solve this error, you must remove old version of modification in Modules> Modifications section before installing.

If you do not have such a module, but ID is same, then you need to change value of parameter `<code>` in installed XML modifier, to make this parameter unique by adding several characters.

6. Error: Warning: DOMDocument :: loadXML (): CData section not finished`

This error means that you are trying to set a too large xml modifier.

Number of characters in `ocmod.xml` file should not exceed 65535.

To solve error, you need to split xml-file of modification into several parts, main thing - do not forget to set each unique value in `<code>`, you can add figures 1,2,3 ... to current value as part identifiers.

Another way to solve is to change type in fields where modifications are stored in database table `` oc_modification ''. You need to execute following SQL query:

```
ALTER TABLE oc_modification CHANGE xml xml MEDIUMTEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;
```
