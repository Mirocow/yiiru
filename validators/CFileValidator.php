<?php
/**
 * Файл класса CFileValidator.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Валидатор CFileValidator проверяет правильность полученного файла.
 *
 * Использует класс модели и имя атрибута для получения информации о загруженном файле.
 * Проверяет успешность загрузки файла, соответствие размера файла и его тип.
 *
 * Валидатор будет пытаться извлечь переданные данные, если атрибут не был установлен ранее.
 * Запомните, что это невозможно при массовом вводе данных:
 * <pre>
 *  foreach($models as $i=>$model)
 *     $model->attribute = CUploadedFile::getInstance($model, "[$i]attribute");
 * </pre>
 * Запомните, что вы должны использовать метод {link CUploadedFile::getInstances} для загрузки нескольких файлов.
 *
 * При использовании CFileValidator с active record-объектами часто используется следующий код:
 * <pre>
 *  if($model->save())
 *  {
 *     // одиночная загрузка
 *     $model->attribute->saveAs($path);
 *     // множественная загрузка
 *     foreach($model->attribute as $file)
 *        $file->saveAs($path);
 *  }
 * </pre>
 *
 * Вы можете использовать {@link CFileValidator} для проверки атрибутов файла.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CFileValidator.php 1470 2009-10-18 19:58:59Z qiang.xue $
 * @package system.validators
 * @since 1.0
 */
class CFileValidator extends CValidator
{
	/**
	 * @var boolean может ли быть значение атрибута пустым или равным null. По умолчанию - true,
	 * т.е. пустой атрибут считается валидным
	 */
	public $allowEmpty=false;
	/**
	 * @var mixed список расширений, допустимых для загрузки.
	 * Он может быть массивом или строкой, содержащей расширения, разделенные пробелом или запятой
	 * (например, "gif, jpg"). Расширения регистронезависимы. По умолчанию - null,
	 * т.е. допустимы любые расширения.
	 */
	public $types;
	/**
	 * @var integer минимальный размер файла в байтах.
	 * По умолчанию - null, т.е. без ограничения.
	 * @see tooSmall
	 */
	public $minSize;
	/**
	 * @var integer максимальный размер файла в байтах.
	 * По умолчанию - null, т.е. без ограничения.
	 * Примечание: максимальный размер также устанавливается свойством 'upload_max_filesize' в файле INI
	 * и скрытым полем 'MAX_FILE_SIZE'.
	 * @see tooLarge
	 */
	public $maxSize;
	/**
	 * @var string сообщение об ошибке, если размер файла слишком большой.
	 * @see maxSize
	 */
	public $tooLarge;
	/**
	 * @var string сообщение об ошибке, если размер файла слишком маленький.
	 * @see minSize
	 */
	public $tooSmall;
	/**
	 * @var string сообщение об ошибке, если загружаемый файл имеет расширение,
	 * не представленное в списке {@link extensions}.
	 */
	public $wrongType;
	/**
	 * @var integer максимальное количество файлов, передаваемых атрибутом.
	 * По умолчанию 1, что значит одиночную загрузку. Определяя более высокое число,
	 * становится возможной множественная загрузка.
	 */
	public $maxFiles=1;
	/**
	 * @var string сообщение об ошибке, если количество загрузок превышает ограничение.
	 */
	public $tooMany;

	/**
	 * Устанавливает атрибут и затем проводит проверку, используя метод {@link validateFile}.
	 * При возникновении ошибки к объекту добавляется сообщение об ошибке.
	 * @param CModel валидируемый объект данных
	 * @param string имя валидируемого атрибута
	 */
	protected function validateAttribute($object, $attribute)
	{
		if($this->maxFiles > 1)
		{
			$files=$object->$attribute;
			if(!is_array($files))
				$files = CUploadedFile::getInstances($object, $attribute);
			if(array()===$files)
				return $this->emptyAttribute($object, $attribute);
			if(count($files) > $this->maxFiles)
			{
				$message=$this->tooMany!==null?$this->tooMany : Yii::t('yii', '{attribute} cannot accept more than {limit} files.');
				$this->addError($object, $attribute, $message, array('{attribute}'=>$attribute, '{limit}'=>$this->maxFiles));
			}
			else
				foreach($files as $file)
					$this->validateFile($object, $attribute, $file);
		}
		else
		{
			$file = $object->$attribute;
			if(!$file instanceof CUploadedFile)
			{
				$file = CUploadedFile::getInstance($object, $attribute);
				if(null===$file)
					return $this->emptyAttribute($object, $attribute);
			}
			$this->validateFile($object, $attribute, $file);
		}
	}

	/**
	 * Внутренняя проверка объекта файла.
	 * @param CModel валидируемый объект
	 * @param string валидируемый атрибут
	 * @param CUploadedFile загруженный файл, переданный для проверки набора правил
	 */
	protected function validateFile($object, $attribute, $file)
	{
		if(null===$file || ($error=$file->getError())==UPLOAD_ERR_NO_FILE)
			return $this->emptyAttribute($object, $attribute);
		else if($error==UPLOAD_ERR_INI_SIZE || $error==UPLOAD_ERR_FORM_SIZE || $this->maxSize!==null && $file->getSize()>$this->maxSize)
		{
			$message=$this->tooLarge!==null?$this->tooLarge : Yii::t('yii','The file "{file}" is too large. Its size cannot exceed {limit} bytes.');
			$this->addError($object,$attribute,$message,array('{file}'=>$file->getName(), '{limit}'=>$this->getSizeLimit()));
		}
		else if($error==UPLOAD_ERR_PARTIAL)
			throw new CException(Yii::t('yii','The file "{file}" was only partially uploaded.',array('{file}'=>$file->getName())));
		else if($error==UPLOAD_ERR_NO_TMP_DIR)
			throw new CException(Yii::t('yii','Missing the temporary folder to store the uploaded file "{file}".',array('{file}'=>$file->getName())));
		else if($error==UPLOAD_ERR_CANT_WRITE)
			throw new CException(Yii::t('yii','Failed to write the uploaded file "{file}" to disk.',array('{file}'=>$file->getName())));
		else if(defined('UPLOAD_ERR_EXTENSION') && $error==UPLOAD_ERR_EXTENSION)  // available for PHP 5.2.0 or above
			throw new CException(Yii::t('yii','File upload was stopped by extension.'));

		if($this->minSize!==null && $file->getSize()<$this->minSize)
		{
			$message=$this->tooSmall!==null?$this->tooSmall : Yii::t('yii','The file "{file}" is too small. Its size cannot be smaller than {limit} bytes.');
			$this->addError($object,$attribute,$message,array('{file}'=>$file->getName(), '{limit}'=>$this->minSize));
		}

		if($this->types!==null)
		{
			if(is_string($this->types))
				$types=preg_split('/[\s,]+/',strtolower($this->types),-1,PREG_SPLIT_NO_EMPTY);
			else
				$types=$this->types;
			if(!in_array(strtolower($file->getExtensionName()),$types))
			{
				$message=$this->wrongType!==null?$this->wrongType : Yii::t('yii','The file "{file}" cannot be uploaded. Only files with these extensions are allowed: {extensions}.');
				$this->addError($object,$attribute,$message,array('{file}'=>$file->getName(), '{extensions}'=>implode(', ',$types)));
			}
		}
	}

	/**
	 * Выдает ошибку для информирования конечного пользователя о пустом атрибуте.
	 * @param CModel валидируемый объект
	 * @param string валидируемый атрибут
	 */
	protected function emptyAttribute($object, $attribute)
	{
		if(!$this->allowEmpty)
		{
			$message=$this->message!==null?$this->message : Yii::t('yii','{attribute} cannot be blank.');
			$this->addError($object,$attribute,$message);
		}
	}

	/**
	 * Возвращает максимально допустимый размер загружаемого файла.
	 * Определение размера основано на трех факторах:
	 * <ul>
	 * <li>'upload_max_filesize' в файле php.ini</li>
	 * <li>скрытое поле 'MAX_FILE_SIZE'</li>
	 * <li>свойство {@link maxSize}</li>
	 * </ul>
	 *
	 * @return integer максимально допустимый размер загружаемого файла.
	 */
	protected function getSizeLimit()
	{
		$limit=ini_get('upload_max_filesize');
		if(strpos($limit,'M')!==false)
			$limit=$limit*1024*1024;
		if($this->maxSize!==null && $limit>0 && $this->maxSize<$limit)
			$limit=$this->maxSize;
		if(isset($_POST['MAX_FILE_SIZE']) && $_POST['MAX_FILE_SIZE']>0 && $_POST['MAX_FILE_SIZE']<$limit)
			$limit=$_POST['MAX_FILE_SIZE'];
		return $limit;
	}
}
