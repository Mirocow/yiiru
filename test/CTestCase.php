<?php
/**
 * Файл класса CTestCase.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2011 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */
 
require_once('PHPUnit/Runner/Version.php');
if(version_compare(PHPUnit_Runner_Version::id(), '3.5.0RC1')<0)
{
require_once('PHPUnit/Framework.php');
require_once('PHPUnit/Framework/TestCase.php');
}
else
{
    require_once('PHPUnit/Autoload.php');
}

/**
 * Класс CTestCase - это базовый класс для всех классов тестовых данных.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CTestCase.php 2799 2011-01-01 19:31:13Z qiang.xue $
 * @package system.test
 * @since 1.1
 */
abstract class CTestCase extends PHPUnit_Framework_TestCase
{
}
