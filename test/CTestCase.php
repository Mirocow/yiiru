<?php
/**
 * Файл класса CTestCase.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
 
require_once('PHPUnit/Framework.php');
require_once('PHPUnit/Framework/TestCase.php');

/**
 * Класс CTestCase - это базовый класс для всех классов тестовых данных.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CTestCase.php 1906 2010-03-14 05:14:31Z qiang.xue $
 * @package system.test
 * @since 1.1
 */
abstract class CTestCase extends PHPUnit_Framework_TestCase
{
}
