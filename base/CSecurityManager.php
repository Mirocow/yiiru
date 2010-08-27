<?php
/**
 * Файл содержит класс, реализующий функцию менеджера безопасности.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * CSecurityManager обеспечивает функции секретных ключей, хеширования и шифрование.
 *
 * CSecurityManager используется компонентами Yii и приложением для целей, связанных с безопасностью.
 * Например, он используется в функции проверки куки (cookie) для предотвращения подделки данных cookie.
 *
 * В основном, CSecurityManager используется для защиты данных от подделки и просмотра.
 * Он может генерировать {@link http://ru.wikipedia.org/wiki/HMAC HMAC}
 * (hash message authentication code, хеш-код идентификации сообщений) и
 * шифровать данные. Секретный ключ, используемый для генерации HMAC,
 * устанавливается свойством {@link setValidationKey ValidationKey}. Ключ, используемый для
 * шифрования данных, устанавливается свойством {@link setEncryptionKey EncryptionKey}. Если эти ключи
 * не установлены явно, генерируются и используются случайные ключи.
 *
 * Для защиты данных с использованием HMAC, вызовите метод {@link hashData()}; а для проверки,
 * подделаны ли данные, вызовите метод {@link validateData()}, который возвращает реальные данные,
 * если они не были подделаны. Алгоритм, используемый для генерации HMAC, определяется свойством
 * {@link validation}.
 *
 * Для шифрования и дешифровки данных используется методы {@link encrypt()} и {@link decrypt()}
 * соответственно, которые используют алгоритм шифрования 3DES. Примечание: должно быть
 * установлено и загружено расширение PHP Mcrypt.
 *
 * CSecurityManager - это компонент ядра приложения, доступный методом
 * {@link CApplication::getSecurityManager()}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CSecurityManager.php 2278 2010-07-21 14:08:46Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
class CSecurityManager extends CApplicationComponent
{
	const STATE_VALIDATION_KEY='Yii.CSecurityManager.validationkey';
	const STATE_ENCRYPTION_KEY='Yii.CSecurityManager.encryptionkey';

	/**
	 * @var string the name of the hashing algorithm to be used by {@link computeHMAC}.
	 * See {@link http://php.net/manual/en/function.hash-algos.php hash-algos} for the list of possible
	 * hash algorithms. Note that if you are using PHP 5.1.1 or below, you can only use 'sha1' or 'md5'.
	 *
	 * Defaults to 'sha1', meaning using SHA1 hash algorithm.
	 * @since 1.1.3
	 */
	public $hashAlgorithm='sha1';
	/**
	 * @var mixed the name of the crypt algorithm to be used by {@link encrypt} and {@link decrypt}.
	 * This will be passed as the first parameter to {@link http://php.net/manual/en/function.mcrypt-module-open.php mcrypt_module_open}.
	 *
	 * This property can also be configured as an array. In this case, the array elements will be passed in order
	 * as parameters to mcrypt_module_open. For example, <code>array('rijndael-256', '', 'ofb', '')</code>.
	 *
	 * Defaults to 'des', meaning using DES crypt algorithm.
	 * @since 1.1.3
	 */
	public $cryptAlgorithm='des';

	private $_validationKey;
	private $_encryptionKey;

	/**
	 * @return string генерирует случайный частный ключ
	 */
	protected function generateRandomKey()
	{
		return rand().rand().rand().rand();
	}

	/**
	 * @return string секретный ключ, используемый для генерации HMAC.
	 * Если ключ явно не установлен, будет сгенерирован и возвращен новый случайный ключ.
	 */
	public function getValidationKey()
	{
		if($this->_validationKey!==null)
			return $this->_validationKey;
		else
		{
			if(($key=Yii::app()->getGlobalState(self::STATE_VALIDATION_KEY))!==null)
				$this->setValidationKey($key);
			else
			{
				$key=$this->generateRandomKey();
				$this->setValidationKey($key);
				Yii::app()->setGlobalState(self::STATE_VALIDATION_KEY,$key);
			}
			return $this->_validationKey;
		}
	}

	/**
	 * @param string ключ, используемый при генерации HMAC
	 * @throws CException вызывается, если ключ пустой
	 */
	public function setValidationKey($value)
	{
		if(!empty($value))
			$this->_validationKey=$value;
		else
			throw new CException(Yii::t('yii','CSecurityManager.validationKey cannot be empty.'));
	}

	/**
	 * @return string секретный ключ, используемый для шифрования/дешифровки данных.
	 * Если ключ явно не установлен, будет сгенерирован и возвращен новый случайный ключ.
	 */
	public function getEncryptionKey()
	{
		if($this->_encryptionKey!==null)
			return $this->_encryptionKey;
		else
		{
			if(($key=Yii::app()->getGlobalState(self::STATE_ENCRYPTION_KEY))!==null)
				$this->setEncryptionKey($key);
			else
			{
				$key=$this->generateRandomKey();
				$this->setEncryptionKey($key);
				Yii::app()->setGlobalState(self::STATE_ENCRYPTION_KEY,$key);
			}
			return $this->_encryptionKey;
		}
	}

	/**
	 * @param string секретный ключ, используемый для шифрования/дешифровки данных.
	 * @throws CException вызывается, если ключ пустой
	 */
	public function setEncryptionKey($value)
	{
		if(!empty($value))
			$this->_encryptionKey=$value;
		else
			throw new CException(Yii::t('yii','CSecurityManager.encryptionKey cannot be empty.'));
	}

	/**
	 * Метод считается устаревшим с версии 1.1.3.
	 * Исользуйте вместо него {@link hashAlgorithm}.
	 */
	public function getValidation()
	{
		return $this->hashAlgorithm;
	}

	/**
	 * Метод считается устаревшим с версии 1.1.3.
	 * Исользуйте вместо него {@link hashAlgorithm}.
	 */
	public function setValidation($value)
	{
		$this->hashAlgorithm=$value;
	}

	/**
	 * Шифрует данные.
	 * @param string шифруемые данные
	 * @param string ключ шифрования. По умолчанию - null, т.е., используется {@link getEncryptionKey EncryptionKey}
	 * @return string шифрованные данные
	 * @throws CException вызывается, если расширение PHP Mcrypt не загружено
	 */
	public function encrypt($data,$key=null)
	{
		$module=$this->openCryptModule();
		$key=substr($key===null ? md5($this->getEncryptionKey()) : $key,0,mcrypt_enc_get_key_size($module));
		srand();
		$iv=mcrypt_create_iv(mcrypt_enc_get_iv_size($module), MCRYPT_RAND);
		mcrypt_generic_init($module,$key,$iv);
		$encrypted=$iv.mcrypt_generic($module,$data);
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);
		return $encrypted;
	}

	/**
	 * Дешифрует данные.
	 * @param string дешифруемые данные
	 * @param string ключ шифрования. По умолчанию - null, т.е., используется {@link getEncryptionKey EncryptionKey}
	 * @return string дешифрованные данные
	 * @throws CException вызывается, если расширение PHP Mcrypt не загружено
	 */
	public function decrypt($data,$key=null)
	{
		$module=$this->openCryptModule();
		$key=substr($key===null ? md5($this->getEncryptionKey()) : $key,0,mcrypt_enc_get_key_size($module));
		$ivSize=mcrypt_enc_get_iv_size($module);
		$iv=substr($data,0,$ivSize);
		mcrypt_generic_init($module,$key,$iv);
		$decrypted=mdecrypt_generic($module,substr($data,$ivSize));
		mcrypt_generic_deinit($module);
		mcrypt_module_close($module);
		return rtrim($decrypted,"\0");
	}

	/**
	 * Открывает модуль mcrypt с конфигурацией, определенной в {@link cryptAlgorithm}.
	 * @return resource дескриптор модуля mycrypt
	 * @since 1.1.3
	 */
	protected function openCryptModule()
	{
		if(extension_loaded('mcrypt'))
		{
			if(is_array($this->cryptAlgorithm))
				$module=call_user_func_array('mcrypt_module_open',$this->cryptAlgorithm);
			else
				$module=mcrypt_module_open($this->cryptAlgorithm, '', MCRYPT_MODE_CBC, '');

			if($module===false)
				throw new CException(Yii::t('yii','Failed to initialize the mcrypt module.'));

			return $module;
		}
		else
			throw new CException(Yii::t('yii','CSecurityManager requires PHP mcrypt extension to be loaded in order to use data encryption feature.'));
	}

	/**
	 * Добавляет префикс в виде HMAC к данным.
	 * @param string хешируемые данные.
	 * @param string частный ключ, испльзуемый для генерации HMAC. По умолчанию - null, т.е., используется {@link validationKey}
	 * @return string данные, с префиксом в виде HMAC
	 */
	public function hashData($data,$key=null)
	{
		return $this->computeHMAC($data,$key).$data;
	}

	/**
	 * Проверяет, поддельные ли данные.
	 * @param string проверяемые данные. Данные должны быть предварительно сгенерированы
	 * методом {@link hashData()}.
	 * @param string частный ключ, испльзуемый для генерации HMAC. По умолчанию - null, т.е., используется {@link validationKey}
	 * @return string реальные данные с префиксом в виде HMAC. False, если данные подделаны
	 */
	public function validateData($data,$key=null)
	{
		$len=strlen($this->computeHMAC('test'));
		if(strlen($data)>=$len)
		{
			$hmac=substr($data,0,$len);
			$data2=substr($data,$len);
			return $hmac===$this->computeHMAC($data2,$key)?$data2:false;
		}
		else
			return false;
	}

	/**
	 * Вычисляет HMAC для данных, используя {@link getValidationKey ValidationKey}.
	 * @param string данные, для которых должен быть сгенерирован HMAC
	 * @param string частный ключ, испльзуемый для генерации HMAC. По умолчанию - null, т.е., используется {@link validationKey}
	 * @return string HMAC для данных
	 */
	protected function computeHMAC($data,$key=null)
	{
		if($key===null)
			$key=$this->getValidationKey();

		if(function_exists('hash_hmac'))
			return hash_hmac($this->hashAlgorithm, $data, $key);

		if(!strcasecmp($this->hashAlgorithm,'sha1'))
		{
			$pack='H40';
			$func='sha1';
		}
		else
		{
			$pack='H32';
			$func='md5';
		}
		$key=str_pad($func($key), 64, chr(0));
		return $func((str_repeat(chr(0x5C), 64) ^ substr($key, 0, 64)) . pack($pack, $func((str_repeat(chr(0x36), 64) ^ substr($key, 0, 64)) . $data)));
	}
}
