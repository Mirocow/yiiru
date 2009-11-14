<?php
/**
 * Файл содержит класс, реализующий функцию менеджера безопасности.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
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
 * {@link setValidation Validation}.
 *
 * Для шифрования и дешифровки данных используется методы {@link encrypt()} и {@link decrypt()}
 * соответственно, которые используют алгоритм шифрования 3DES. Примечание: должно быть
 * установлено и загружено расширение PHP Mcrypt.
 *
 * CSecurityManager - это компонент ядра приложения, доступный методом
 * {@link CApplication::getSecurityManager()}.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CSecurityManager.php 434 2008-12-30 23:14:31Z qiang.xue $
 * @package system.base
 * @since 1.0
 */
class CSecurityManager extends CApplicationComponent
{
	const STATE_VALIDATION_KEY='Yii.CSecurityManager.validationkey';
	const STATE_ENCRYPTION_KEY='Yii.CSecurityManager.encryptionkey';

	private $_validationKey;
	private $_encryptionKey;
	private $_validation='SHA1';

	/**
	 * @return string генерирует случайный ключ
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
	 * @return string алгоритм хеширования, используемый для генерации HMAC. По умолчанию - 'SHA1'.
	 */
	public function getValidation()
	{
		return $this->_validation;
	}

	/**
	 * @param string алгоритм хеширования, используемый для генерации HMAC. Должен быть либо 'MD5' либо 'SHA1'.
	 */
	public function setValidation($value)
	{
		if($value==='MD5' || $value==='SHA1')
			$this->_validation=$value;
		else
			throw new CException(Yii::t('yii','CSecurityManager.validation must be either "MD5" or "SHA1".'));
	}

	/**
	 * Шифрует данные, используя ключ {@link getEncryptionKey EncryptionKey}.
	 * @param string шифруемые данные
	 * @return string шифрованные данные
	 * @throws CException вызывается, если расширение PHP Mcrypt не загружено
	 */
	public function encrypt($data)
	{
		if(extension_loaded('mcrypt'))
		{
			$module=mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
			$key=substr(md5($this->getEncryptionKey()),0,mcrypt_enc_get_key_size($module));
			srand();
			$iv=mcrypt_create_iv(mcrypt_enc_get_iv_size($module), MCRYPT_RAND);
			mcrypt_generic_init($module,$key,$iv);
			$encrypted=$iv.mcrypt_generic($module,$data);
			mcrypt_generic_deinit($module);
			mcrypt_module_close($module);
			return $encrypted;
		}
		else
			throw new CException(Yii::t('yii','CSecurityManager requires PHP mcrypt extension to be loaded in order to use data encryption feature.'));
	}

	/**
	 * Дешифрует данные, используя ключ {@link getEncryptionKey EncryptionKey}.
	 * @param string дешифруемые данные
	 * @return string дешифрованные данные
	 * @throws CException вызывается, если расширение PHP Mcrypt не загружено
	 */
	public function decrypt($data)
	{
		if(extension_loaded('mcrypt'))
		{
			$module=mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
			$key=substr(md5($this->getEncryptionKey()),0,mcrypt_enc_get_key_size($module));
			$ivSize=mcrypt_enc_get_iv_size($module);
			$iv=substr($data,0,$ivSize);
			mcrypt_generic_init($module,$key,$iv);
			$decrypted=mdecrypt_generic($module,substr($data,$ivSize));
			mcrypt_generic_deinit($module);
			mcrypt_module_close($module);
			return rtrim($decrypted,"\0");
		}
		else
			throw new CException(Yii::t('yii','CSecurityManager requires PHP mcrypt extension to be loaded in order to use data encryption feature.'));
	}

	/**
	 * Добавляет префикс в виде HMAC к данным.
	 * @param string хешируемые данные.
	 * @return string данные, с префиксом в виде HMAC
	 */
	public function hashData($data)
	{
		$hmac=$this->computeHMAC($data);
		return $hmac.$data;
	}

	/**
	 * Проверяет, поддельные ли данные.
	 * @param string проверяемые данные. Данные должны быть предварительно сгенерированы
	 * методом {@link hashData()}.
	 * @return string реальные данные с префиксом в виде HMAC. False, если данные подделаны
	 */
	public function validateData($data)
	{
		$len=$this->_validation==='SHA1'?40:32;
		if(strlen($data)>=$len)
		{
			$hmac=substr($data,0,$len);
			$data2=substr($data,$len);
			return $hmac===$this->computeHMAC($data2)?$data2:false;
		}
		else
			return false;
	}

	/**
	 * Вычисляет HMAC для данных, используя {@link getValidationKey ValidationKey}.
	 * @param string данные, для которых должен быть сгенерирован HMAC
	 * @return string HMAC для данных
	 */
	protected function computeHMAC($data)
	{
		if($this->_validation==='SHA1')
		{
			$pack='H40';
			$func='sha1';
		}
		else
		{
			$pack='H32';
			$func='md5';
		}
		$key=$this->getValidationKey();
		$key=str_pad($func($key), 64, chr(0));
		return $func((str_repeat(chr(0x5C), 64) ^ substr($key, 0, 64)) . pack($pack, $func((str_repeat(chr(0x36), 64) ^ substr($key, 0, 64)) . $data)));
	}
}
