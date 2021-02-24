<?php
/**
 * note - use class Chat
 * *Мультизагрузка файлов c защитой от исполняемых файлов и перезаписи дубликатов
 * доработано snipp.ru/php/uploads-files
 * @param pathname path to remote dir
 * @param input_name name of input type=file
 * *Usage
 * ?optional
 * ? Uploads::staticProperty = 'new value';
 * ???
 * $upload = new Uploads(opt $pathname, opt input_name);
 *
 */

class Uploads
{
	public static
		$log,
	// Название <input type="file">
		$input_name = 'file',
	// Разрешенные расширения файлов.
		$allow = [],
	// Запрещенные расширения файлов.
		$deny = [
			'phtml', 'php', 'php3', 'php4', 'php5', 'php6', 'php7', 'phps', 'cgi', 'pl', 'asp',
			'aspx', 'shtml', 'shtm', 'htaccess', 'htpasswd', 'ini', 'log', 'sh', 'js', 'html',
			'htm', 'css', 'sql', 'spl', 'scgi', 'fcgi'
		],
	// Директория куда будут загружаться файлы.
		$pathname = \DR . '/files';

	public
		$fileNames= [],
		// *Загруженные файлы
		$loaded = [];

	protected
		$error = [],
		$success = [],
		$files = [];


	public function __construct($pathname=null, $input_name=null)
	{
		global $log;
		self::$log = $log;
		// if(!Index_my_addon::is_adm())
		// 	die('Access denied to ' . __FILE__);

		self::$pathname = $pathname ?? static::$pathname;
		self::$input_name = $input_name ?? static::$input_name;

		if (empty($_FILES[static::$input_name]))
			return;

		if(
			!is_dir(static::$pathname)
			&& !mkdir(static::$pathname, 0777, 1)
		)
			die("Невозможно создать " . static::$pathname);

		$this->_checkFiles();

		// Логгируем сообщение о результате загрузки.
		self::$log->add(
			__METHOD__,null,
			[$this->getResult()]
		);
	}


	/**
	 * *Проверяем мультизагрузку и формируем $this->files
	 */
	private function _checkFiles()
	{
		// Преобразуем массив $_FILES в удобный вид для перебора в foreach.

		$diff = count($_FILES[static::$input_name]) - count($_FILES[static::$input_name], COUNT_RECURSIVE);

		if ($diff == 0)
		{
			$this->files[] = $_FILES[static::$input_name];
		}
		else
		// *Мультизагрузка
		{
			foreach($_FILES[static::$input_name] as $k => $l)
			{

				foreach($l as $i => $v)
				{
					$this->files[$i][$k] = $v;
				}

			}
		}

		foreach ($this->files as $file)
		{
			$this->fileNames[]= $file['name'];
			$this->_iterFiles($file);
		}
	}


	private function _prefixDuplicated($file, $name, $parts)
	{
		$i = 0;
		$prefix = '';

		while (is_file(self::$pathname . '/' . $parts['filename'] . $prefix . '.' . $parts['extension']))
		{
			$prefix = '(' . ++$i . ')';
		}

		$name = $parts['filename'] . $prefix . '.' . $parts['extension'];

		// Перемещаем файл в директорию.
		if (move_uploaded_file($file['tmp_name'], self::$pathname . '/' . $name))
		{
			// Далее можно сохранить название файла в БД и т.п.
			$this->success []= 'Файл «' . self::$pathname."/$name" . '» успешно загружен.';
			$this->loaded[]= self::$pathname."/$name";
		}
		else
		{
			$this->error []= 'Не удалось загрузить файл ' . $name;
		}

	}


	private function _iterFiles($file)
	{
		// Проверим на ошибки загрузки.

		if (!empty($file['error']) || empty($file['tmp_name']))
		{
			switch (@$file['error']) {
				case 1:
				case 2: $this->error []= 'Превышен размер загружаемого файла. Максимально допустимый размер - ' . self::getMaxSizeUpload()/1024 . 'kB'; break;
				case 3: $this->error []= 'Файл был получен только частично.'; break;
				case 4: $this->error []= 'Файл не был загружен.'; break;
				case 6: $this->error []= 'Файл не загружен - отсутствует временная директория.'; break;
				case 7: $this->error []= 'Не удалось записать файл на диск.'; break;
				case 8: $this->error []= 'PHP-расширение остановило загрузку файла.'; break;
				case 9: $this->error []= 'Файл не был загружен - директория не существует.'; break;
				case 10: $this->error []= 'Превышен максимально допустимый размер файла.'; break;
				case 11: $this->error []= 'Данный тип файла запрещен.'; break;
				case 12: $this->error []= 'Ошибка при копировании файла.'; break;
				default: $this->error []= 'Файл не был загружен - неизвестная ошибка.'; break;

			}

		}
		elseif ($file['tmp_name'] == 'none' || !is_uploaded_file($file['tmp_name']))
		{
			$this->error []= 'Не удалось загрузить файл.';
		}
		else
		{
			$name = Chat::translit($file['name']);

			// Оставляем в имени файла только буквы, цифры и некоторые символы.
			$pattern = "~[^a-zа-яё0-9,\~!@#%^-_\$\?\(\)\{\}\[\]\.]|[-]+~iu";

			$name = preg_replace($pattern, '-', $name);

			$parts = pathinfo($name);

			if (empty($name) || empty($parts['extension']))
			{
				$this->error []= 'Недопустимый тип файла';
			}
			elseif (!empty(static::$allow) && !in_array(strtolower($parts['extension']), static::$allow))
			{
				$this->error []= 'Недопустимый тип файла';
			}
			elseif (!empty(static::$deny) && in_array(strtolower($parts['extension']), static::$deny))
			{
				$this->error []= 'Недопустимый тип файла';
			}
			else
			{
				$this->_prefixDuplicated($file, $name, $parts);
			}

		}
	}


	/**
	 * Вычисление максимальной загрузки
	 */
	public static function getMaxSizeUpload ()
	{
		return min(self::sizeToBytes(ini_get('post_max_size')), self::sizeToBytes(ini_get('upload_max_filesize')));
	}

	protected static function sizeToBytes ($sSize)
	{
		$sSuffix = strtoupper(substr($sSize, -1));
	   if (!in_array($sSuffix, ['P','T','G','M','K']))
		 return (int)$sSize;

	   $iValue = substr($sSize, 0, -1);
	   switch ($sSuffix) {
			case 'P':
				$iValue *= 1024;
			case 'T':
				$iValue *= 1024;
			case 'G':
				$iValue *= 1024;
			case 'M':
				$iValue *= 1024;
			case 'K':
				$iValue *= 1024;
				break;
	   }
	   return (int)$iValue;
	}

	public function checkSuccess()
	{
		return count($this->success);
	}

	public function getResult()
	{
		return !empty($this->success) ?
			$this->success :
			$this->error;
	}

}
