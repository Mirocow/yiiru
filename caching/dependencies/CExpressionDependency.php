<?php
/**
 * Файл класса CExpressionDependency.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2008-2009 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

/**
 * Компонент CExpressionDependency представляет зависимость, основанную на результате PHP-выражения.
 *
 * Компонент CExpressionDependency выполняет проверку, основанную на результате {@link expression PHP-выражения}.
 * Зависимость является неизменной только в том случае, если результат такой же как
 * результат, вычисленный при сохранении данных в кэш.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: CExpressionDependency.php 891 2009-03-25 15:20:56Z qiang.xue $
 * @package system.caching.dependencies
 * @since 1.0
 */
class CExpressionDependency extends CCacheDependency
{
	/**
	 * @var string PHP-выражение, результат которого используется для определения зависимости.
	 */
	public $expression;

	/**
	 * Конструктор.
	 * @param string PHP-выражение, результат которого используется для определения зависимости.
	 */
	public function __construct($expression='true')
	{
		$this->expression=$expression;
	}

	/**
	 * Генерирует данные, необходимые для определения изменения зависимости.
	 * Метод возвращает результат PHP-выражения.
	 * @return mixed данные, необходимые для определения изменения зависимости
	 */
	protected function generateDependentData()
	{
		return @eval('return '.$this->expression.';');
	}
}
